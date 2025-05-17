import Chart from 'chart.js/auto';
import {isRTL,hexRGB} from "../function";

export default function Doughnut({selector, data, tooltip}){
    let _get_data = data;
    const selectCanvas = document.querySelector(selector);
    
    let tooltipConfig = {
        tooltipDefault : {
            enabled: true,
            rtl: isRTL(),
            callbacks: {
                label: function (context) {
                    return `${context.parsed} ${_get_data.dataUnit}`;
                },
            },
            backgroundColor: '#fff',
            borderColor: '#eff6ff',
            borderWidth: 2,
            titleFont:{
                size: 13,
            },
            titleColor: '#6783b8',
            titleMarginBottom: 6,
            bodyColor: '#9eaecf',
            bodyFont:{
                size: 12
            },
            bodySpacing: 4,
            padding: 10,
            footerMarginTop: 0,
            displayColors: false
        },
    
        tooltipDark : {
            enabled: true,
            rtl: isRTL(),
            callbacks: {
                label: function (context) {
                    return `${context.parsed} ${_get_data.dataUnit}`;
                },
            },
            backgroundColor: '#1c2b46',
            titleFont:{
                size: 13,
            },
            titleColor: '#fff',
            titleMarginBottom: 6,
            bodyColor: '#fff',
            bodyFont:{
                size: 12
            },
            bodySpacing:4,
            padding: 10,
            footerMarginTop: 0,
            displayColors: false
        },
    }

    const tooltipStyle = tooltip ? tooltipConfig[tooltip] : tooltipConfig['tooltipDefault'];

    const chart_data = [];
    for (let i = 0; i < _get_data.datasets.length; i++) {
        chart_data.push({
            backgroundColor: _get_data.datasets[i].background,
            borderWidth: 2,
            borderColor: _get_data.datasets[i].borderColor,
            hoverBorderColor: _get_data.datasets[i].borderColor,
            data: _get_data.datasets[i].data,
        });
    } 
    const chart = selectCanvas && new Chart(selectCanvas.getContext("2d"), {
        type: 'doughnut',
        data: {
            labels: _get_data.labels,
            datasets: chart_data,
        },
        options: {
            plugins: {
                legend: {
                    display: (_get_data.legend) ? _get_data.legend : false,
                    labels: {
                        boxWidth: 12,
                        padding: 20,
                        color: '#6783b8',
                    }
                },
                tooltip: tooltipStyle,
            },
            rotation: -1.5,
            cutoutPercentage: 70,
            maintainAspectRatio: false,
        }
    });
}