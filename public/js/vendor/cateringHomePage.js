// Chart: Sales mingguan
const ctx = document.getElementById('salesChart')?.getContext('2d');
const chartData = window.chartData || []; // Data di-pass dari blade

if (ctx && chartData.length) {
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: [
                locale.week_1,
                locale.week_2,
                locale.week_3,
                locale.week_4,
            ],
            datasets: [{
                data: chartData,
                borderColor: '#000',
                backgroundColor: 'transparent',
                pointBackgroundColor: 'rgba(0,128,0,1)',
                pointRadius: 6,
            }]
        },
        options: {
            animation: {
                duration: 1500,
                easing: 'easeOutQuart'
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: 'rgba(0,128,0,.7)',
                        callback: v => 'Rp' + v.toLocaleString('id-ID')
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: 'rgba(0,128,0,.7)'
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            },
            responsive: true,
            maintainAspectRatio: false
        }
    });
}

// Animasi daun
const leafCount = 5;
for (let i = 0; i < leafCount; i++) {
    const leaf = document.createElement('div');
    leaf.classList.add('leaf');
    leaf.style.left = Math.random() * 100 + 'vw';
    leaf.style.animationDuration = (8 + Math.random() * 5) + 's';
    leaf.style.animationDelay = (Math.random() * 5) + 's';
    leaf.style.transform = `translateX(${Math.random() * 100}px)`;
    document.body.appendChild(leaf);
}
