
        /* ambil <canvas> */
        const ctx = document.getElementById('salesChart').getContext('2d');

        /* data mingguan  – datang dari controller                       */
        /* $salesData sudah berisi array 4 elemen → [week1, week2, …]    */
        const chartData = @json($salesData);

        /* build chart */
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: [
                    '{{ __('catering-home-page.week_1') }}',
                    '{{ __('catering-home-page.week_2') }}',
                    '{{ __('catering-home-page.week_3') }}',
                    '{{ __('catering-home-page.week_4') }}',
                ],
                datasets: [{
                    data: chartData, // ⬅️ pakai data dinamis
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
        const leafCount = 5;
        for (let i = 0; i < leafCount; i++) {
            const leaf = document.createElement('div');
            leaf.classList.add('leaf');
            leaf.style.left = Math.random() * 100 + 'vw';
            leaf.style.animationDuration = (8 + Math.random() * 5) + 's';
            leaf.style.animationDelay = (Math.random() * 5) + 's';
            leaf.style.transform = `translateX(${Math.random() * 100}px)`; // posisi awal random
            document.body.appendChild(leaf);
        }
