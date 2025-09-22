import Chart from 'chart.js/auto';

document.addEventListener('DOMContentLoaded', function() {
    const isDarkMode = document.documentElement.classList.contains('dark');
    const gridColor = isDarkMode ? '#374151' : '#f1f5f9';
    const textColor = isDarkMode ? '#d1d5db' : '#6b7280';

    // ✅ GRAPHIQUE 1 : Taux de Réussite Mensuel
    const tauxReussiteCtx = document.getElementById('tauxReussiteChart')?.getContext('2d');
    if (tauxReussiteCtx) {
        const chartDataTauxReussite = window.chartData?.tauxReussite || Array(12).fill(0);
        const chartDataAdmis = window.chartData?.admis || Array(12).fill(0);
        const chartDataMoyennes = window.chartData?.moyennes || Array(12).fill(10);
        const selectedChartType = window.chartConfig?.type || 'line';

        const tauxReussiteChart = new Chart(tauxReussiteCtx, {
            type: selectedChartType,
            data: {
                labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'],
                datasets: [
                    {
                        label: 'Taux de Réussite (%)',
                        data: chartDataTauxReussite,
                        borderColor: '#10b981',
                        backgroundColor: '#10b98120',
                        tension: 0.4,
                        fill: true,
                        yAxisID: 'y1'
                    },
                    {
                        label: 'Nombre d\'Admis',
                        data: chartDataAdmis,
                        borderColor: '#3b82f6',
                        backgroundColor: '#3b82f620',
                        tension: 0.4,
                        fill: false,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Moyenne Générale',
                        data: chartDataMoyennes,
                        borderColor: '#8b5cf6',
                        backgroundColor: '#8b5cf620',
                        tension: 0.4,
                        fill: false,
                        yAxisID: 'y2'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    title: {
                        display: true,
                        text: 'Évolution des Performances Académiques',
                        color: textColor
                    },
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true,
                            color: textColor
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label === 'Taux de Réussite (%)') {
                                    return label + ': ' + context.parsed.y + '%';
                                } else if (label === 'Moyenne Générale') {
                                    return label + ': ' + context.parsed.y + '/20';
                                } else {
                                    return label + ': ' + context.parsed.y + ' étudiants';
                                }
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: textColor
                        }
                    },
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Nombre d\'Étudiants',
                            color: textColor
                        },
                        grid: {
                            color: gridColor
                        },
                        ticks: {
                            color: textColor
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        beginAtZero: true,
                        max: 100,
                        title: {
                            display: true,
                            text: 'Taux de Réussite (%)',
                            color: textColor
                        },
                        grid: {
                            drawOnChartArea: false,
                        },
                        ticks: {
                            color: textColor,
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    },
                    y2: {
                        type: 'linear',
                        display: false,
                        position: 'right',
                        min: 0,
                        max: 20,
                        grid: {
                            drawOnChartArea: false,
                        }
                    }
                }
            }
        });

        // Stocker la référence globalement pour Livewire
        window.tauxReussiteChart = tauxReussiteChart;
    }

    // ✅ GRAPHIQUE 2 : Performance Académique
    const performanceCtx = document.getElementById('performanceChart')?.getContext('2d');
    if (performanceCtx) {
        const etudiantsAdmis = window.chartData?.etudiantsAdmis || 0;
        const rattrapage = window.chartData?.rattrapage || 0;
        const redoublants = window.chartData?.redoublants || 0;
        const exclus = window.chartData?.exclus || 0;

        const performanceChart = new Chart(performanceCtx, {
            type: 'doughnut',
            data: {
                labels: ['Admis', 'Rattrapage', 'Redoublants', 'Exclus'],
                datasets: [{
                    data: [etudiantsAdmis, rattrapage, redoublants, exclus],
                    backgroundColor: [
                        '#10b981',
                        '#f59e0b',
                        '#f97316',
                        '#ef4444'
                    ],
                    borderWidth: 3,
                    borderColor: isDarkMode ? '#111827' : '#ffffff',
                    hoverBorderWidth: 4,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '50%',
                plugins: {
                    title: {
                        display: true,
                        text: 'Répartition des Résultats Scolaires',
                        color: textColor,
                        padding: 20
                    },
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            usePointStyle: true,
                            color: textColor,
                            generateLabels: function(chart) {
                                const data = chart.data;
                                if (data.labels.length && data.datasets.length) {
                                    const total = data.datasets[0].data.reduce((a, b) => a + b, 0);
                                    return data.labels.map((label, i) => {
                                        const value = data.datasets[0].data[i];
                                        const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                        return {
                                            text: `${label}: ${value} (${percentage}%)`,
                                            fillStyle: data.datasets[0].backgroundColor[i],
                                            pointStyle: 'circle',
                                            hidden: false,
                                            index: i
                                        };
                                    });
                                }
                                return [];
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                return `${label}: ${value} étudiants (${percentage}%)`;
                            }
                        }
                    }
                },
                animation: {
                    animateRotate: true,
                    animateScale: true,
                    duration: 1500,
                    easing: 'easeOutBounce'
                }
            }
        });

        window.performanceChart = performanceChart;
    }

    // ✅ Mise à jour dynamique des graphiques pour Livewire
    if (window.Livewire) {
        Livewire.on('dashboard-refreshed', () => {
            if (window.tauxReussiteChart) {
                window.tauxReussiteChart.config.type = window.chartConfig?.type || 'line';
                window.tauxReussiteChart.update('active');
            }
            if (window.performanceChart) {
                window.performanceChart.update('active');
            }
        });
    }

    // ✅ Animation au scroll
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -100px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const canvas = entry.target.querySelector('canvas');
                if (canvas) {
                    canvas.style.opacity = '1';
                    canvas.style.transform = 'translateY(0)';
                }
            }
        });
    }, observerOptions);

    document.querySelectorAll('.bg-white.dark\\:bg-gray-900').forEach(chart => {
        const canvas = chart.querySelector('canvas');
        if (canvas) {
            canvas.style.opacity = '0';
            canvas.style.transform = 'translateY(20px)';
            canvas.style.transition = 'all 0.6s ease-out';
            observer.observe(chart);
        }
    });
});