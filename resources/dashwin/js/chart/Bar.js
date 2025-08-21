import Chart from 'chart.js/auto';
import {isRTL,hexRGB} from "../function";

export default function Bar({selector, data, tooltip, scales, axis, thickness }){
    let _get_data = data;
    const selectCanvas = document.querySelector(selector);
    let tooltipConfig = {
        tooltipDefault : {
            enabled: true,
            rtl: isRTL(),
            callbacks: {
                label: function (context) {
                    return `${axis === 'x' ? context.parsed.y : axis === 'y' ? context.parsed.x : context.parsed.y } ${_get_data.dataUnit}`;
                },
            },
            backgroundColor: '#eff6ff',
            titleFont:{
                size: 13,
            },
            titleColor: '#6783b8',
            titleMarginBottom: 6,
            bodyColor: '#9eaecf',
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
                    return `${axis === 'x' ? context.parsed.y : axis === 'y' ? context.parsed.x : context.parsed.y } ${_get_data.dataUnit}`;
                },
            },
            backgroundColor: '#eff6ff',
            titleFont:{
                size: 11,
            },
            titleColor: '#6783b8',
            titleMarginBottom: 4,
            bodyColor: '#9eaecf',
            bodyFont:{
                size:10,
            },
            bodySpacing:3,
            padding: 8,
            footerMarginTop: 0,
            displayColors: false
        },
    }

    const tooltipStyle = tooltip ? tooltipConfig[tooltip] : tooltipConfig['tooltipDefault'];

    let scaleConfig =  {
        scalesDefault : {
            y: {
                display: true,
                position: isRTL() ? "right" : "left",
                stacked: (_get_data.stacked) ? _get_data.stacked : false,
                ticks: {
                    beginAtZero: true,
                    font:{
                        size:12,
                    },
                    color: '#9eaecf',
                    padding: 10,
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
                stacked: (_get_data.stacked) ? _get_data.stacked : false,
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
                    zeroLineColor: 'transparent',
                    offset: true,
                    drawTicks:false,
                }
            }
        },
    
        noScale : {
            y: {
                display: false,
                stacked: (_get_data.stacked) ? _get_data.stacked : false,
            },
            x: {
                display: false,
                stacked: (_get_data.stacked) ? _get_data.stacked : false,
                reverse: isRTL()
            }
        },
    
        noScaleLine : {
            y: {
                display: true,
                position: isRTL() ? "right" : "left",
                stacked: (_get_data.stacked) ? _get_data.stacked : false,
                ticks: {
                    beginAtZero: false,
                    display: false,
                    stepSize: 300
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
                reverse: isRTL()
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
                    min:100,
                    max:5000,
                    stepSize:1200
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
                    padding:10,
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
    }

    let scalesStyle = scales ? scaleConfig[scales] : scaleConfig['scalesDefault'];

    const chart_data = [];
    for (let i = 0; i < _get_data.datasets.length; i++) {
        chart_data.push({
            label: _get_data.datasets[i].label,
            tension: _get_data.lineTension,
            backgroundColor: _get_data.datasets[i].color,
            borderWidth: 2,
            borderColor: (thickness === "thin" || thickness === "thick") ? "transparent" : _get_data.datasets[i].color,
            data: _get_data.datasets[i].data,
            barPercentage: thickness === "thin" ? .5 : thickness === "thick" ? .85 : .7,
            categoryPercentage:thickness === "thin" ? .7 : thickness === "thick" ? .9 :  .7
        });
    } 

    const chart =  selectCanvas && new Chart(selectCanvas.getContext("2d"), {
        type: 'bar',
        data: {
            labels: _get_data.labels,
            datasets: chart_data,
        },
        options: {
            indexAxis: axis ? axis : 'x',
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