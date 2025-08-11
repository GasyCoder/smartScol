import Chart from 'chart.js/auto';
import {isRTL,hexRGB} from "../function";

export default function Line({selector, data, tooltip, scales}){
    let _get_data = data;
    const selectCanvas = document.querySelector(selector);
    let tooltipConfig = {
        tooltipDefault : {
            enabled: true,
            rtl: isRTL(),
            callbacks: {
                label: function (context) {
                    return `${context.parsed.y} ${_get_data.dataUnit}`;
                },
            },
            backgroundColor: '#eff6ff',
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
                    return `${context.parsed.y} ${_get_data.dataUnit}`;
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
    
        tooltipSmall : {
            enabled: true,
            rtl: isRTL(),
            callbacks: {
                title: function() {
                    return false;
                },
                label: function (context) {
                    return `${context.parsed.y} ${_get_data.dataUnit}`;
                },
            },
            backgroundColor: '#eff6ff',
            titleFont:{
                size: 11,
            },
            titleColor: '#6783b8',
            titleMarginBottom: 6,
            bodyColor: '#9eaecf',
            bodyFont:{
                size:9,
            },
            bodySpacing: 4,
            padding:6,
            footerMarginTop: 0,
            displayColors: false
        },
    
        tooltipSmallDark : {
            enabled: true,
            rtl: isRTL(),
            callbacks: {
                title: function() {
                    return false;
                },
                label: function (context) {
                    return `${context.parsed.y} ${_get_data.dataUnit}`;
                },
            },
            backgroundColor: '#1c2b46',
            titleFont:{
                size: 13,
            },
            titleColor: '#fff',
            titleMarginBottom: 4,
            bodyColor: '#fff',
            bodyFont:{
                size: 8
            },
            bodySpacing:4,
            padding: 6,
            footerMarginTop: 0,
            displayColors: false
        },
    }

    const tooltipStyle = tooltip ? tooltipConfig[tooltip] : tooltipConfig['tooltipDefault'];

    let scaleConfig = {
        scalesDefault : {
            y: {
                display: true,
                position: isRTL() ? "right" : "left",
                ticks: {
                    beginAtZero: true,
                    font:{
                        size:12,
                    },
                    color: '#9eaecf',
                    padding: 15,
                },
                grid: {
                    color: hexRGB("#526484", .2),
                    tickLength: 0,
                    zeroLineColor: hexRGB("#526484", .2),
                    drawTicks:false,
                },
            },
            x: {
                display: true,
                ticks: {
                    font:{
                        size:12,
                    },
                    color: '#9eaecf',
                    source: 'auto',
                    padding: 10,
                },
                reverse: isRTL(),
                grid: {
                    color: "transparent",
                    tickLength: 10,
                    zeroLineColor: hexRGB("#526484",.2),
                    offset: false,
                    drawTicks:false,
                }
            }
        },
    
        noScale : {
            y: {
                display: false,
            },
            x: {
                display: false,
            }
        },
    
        scales2 : {
            y: {
                display: true,
                stacked: (_get_data.stacked) ? _get_data.stacked : false,
                position : isRTL() ? "right" : "left",
                ticks: {
                    beginAtZero:true,
                    font:{
                        size:11,
                    },
                    color:'#9eaecf',
                    padding:10,
                    callback: function(value, index, values) {
                        return '$ ' + value;
                    },
                    min:0,
                    stepSize:3000
                },
                grid: { 
                    color: hexRGB("#526484",.2),
                    tickLength:0,
                    zeroLineColor: hexRGB("#526484",.2),
                    drawTicks:false,
                },
            },
            x: {
                display: false,
                stacked: (_get_data.stacked) ? _get_data.stacked : false,
                ticks: {
                    font:{
                        size:9,
                    },
                    color:'#9eaecf',
                    source: 'auto',
                    padding:10,
                },
                reverse: isRTL(),
                grid: {
                    color: "transparent",
                    tickLength: 0,
                    zeroLineColor: 'transparent',
                    drawTicks:false,
                },
            }
        },
    
        scales3 : {
            y: {
                display: true,
                stacked: (_get_data.stacked) ? _get_data.stacked : false,
                position : isRTL() ? "right" : "left",
                ticks: {
                    beginAtZero:true,
                    font:{
                        size:11,
                    },
                    color:'#9eaecf',
                    padding:10,
                    callback: function(value, index, values) {
                        return '$ ' + value;
                    },
                    min:100,
                    stepSize:3000
                },
                grid: { 
                    color: hexRGB("#526484",.2),
                    tickLength:0,
                    zeroLineColor: hexRGB("#526484",.2),
                    drawTicks:false,
                },
                
            },
            x: {
                display: true,
                stacked: (_get_data.stacked) ? _get_data.stacked : false,
                ticks: {
                    font:{
                        size:9,
                    },
                    color:'#9eaecf',
                    source: 'auto',
                    padding: 10,
                },
                reverse : isRTL(),
                grid: {
                    color: "transparent",
                    tickLength: 0,
                    zeroLineColor: 'transparent',
                    drawTicks:false,
                },
            }
        },
    
        scales4 : {
            y: {
                display: true,
                position: isRTL() ? "right" : "left",
                ticks: {
                    beginAtZero: true,
                    font:{
                        size:12,
                    },
                    color: '#9eaecf',
                    padding: 8,
                    stepSize: 2400
                },
                grid: {
                    color: hexRGB("#526484", .2),
                    tickLength: 0,
                    zeroLineColor: hexRGB("#526484", .2),
                    drawTicks:false,
                },
            },
            x: {
                display: false,
                ticks: {
                    font:{
                        size:12,
                    },
                    color: '#9eaecf',
                    source: 'auto',
                    padding: 0,
                },
                reverse: isRTL(),
                grid: {
                    color: "transparent",
                    tickLength: 0,
                    zeroLineColor: 'transparent',
                    offset: true,
                    drawTicks:false,
                }
            }
        },
    }
    let scalesStyle = scales ? scaleConfig[scales] : scaleConfig['scalesDefault'];

    const chart_data = [];
    for (let i = 0; i < _get_data.datasets.length; i++) {
        chart_data.push({
            label: _get_data.datasets[i].label,
            tension: _get_data.lineTension,
            backgroundColor: _get_data.datasets[i].background,
            fill: true,
            borderWidth: 2,
            borderDash: _get_data.datasets[i].dash,
            borderColor: _get_data.datasets[i].color,
            pointBorderColor: _get_data.point === true ? _get_data.datasets[i].color : "transparent",
            pointBackgroundColor: _get_data.point === true ? '#fff' : "transparent",
            pointHoverBackgroundColor: "#fff",
            pointHoverBorderColor: _get_data.datasets[i].color,
            pointBorderWidth: 2,
            pointHoverRadius: 4,
            pointHoverBorderWidth: 2,
            pointRadius: 4,
            pointHitRadius: 4,
            data: _get_data.datasets[i].data,
        });
    } 
    const chart = selectCanvas && new Chart(selectCanvas.getContext("2d"), {
        type: 'line',
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
            maintainAspectRatio: false,
            scales: scalesStyle
        }
    });
}