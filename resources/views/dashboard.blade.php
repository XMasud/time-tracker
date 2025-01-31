<x-app-layout>

        <x-slot name="header">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Dashboard') }}
            </h2>
        </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="grid grid-cols-2 gap-4 p-4">

                        <div class="bg-white shadow rounded-2xl p-6">

                            <canvas id="myBarChart" class="w-full max-h-96"></canvas>
                        </div>

                        <div class="bg-white shadow rounded-2xl p-6">

                            <canvas id="myLineChart" class="w-full max-h-96"></canvas>
                        </div>

                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <div class="grid grid-cols-2 gap-4 p-4">
                        <!-- Card 1 -->
                        <div class="bg-white border border-gray-200 rounded-2xl shadow p-6" style="background-color: darkkhaki;">
                            <h1 class="text-lg font-bold mb-4" style="text-align: center;">Weekly Working Hours</h1>
                            <h3 class="text-lg font-bold" style="text-align: center;">{{$weeklyHours}} Hours</h3>
                        </div>

                        <!-- Card 2 -->
                        <div class="bg-white border border-gray-200 rounded-2xl shadow p-6" style="background-color: darkkhaki;">
                            <h1 class="text-lg font-bold mb-4" style="text-align: center;">Monthly Working Hours</h1>
                            <h3 class="text-lg font-bold" style="text-align: center;">{{$monthlyHours}} Hours</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">

                @if (session('success'))
                    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 2000)" class="p-4 mb-4 bg-green-100 text-green-800 border border-green-300 rounded-md">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 2000)" class="p-4 mb-4 bg-red-100 text-red-800 border border-red-300 rounded-md">
                        {{ session('error') }}
                    </div>
                @endif

                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('check-in-out') }}">
                        @csrf

                        @if(!empty($last_activity['check_out']))
                            <div class="mb-4 text-gray-900">
                                <h3 class="text-lg font-bold mb-2">Enter your log</h3>
                            </div>
                            <div class="mb-4">
                                <label for="datetime" class="block text-sm font-medium text-gray-700">Select Date and
                                    Time</label>
                                <input type="text" id="datetime" name="checkin"
                                       class="mt-1 block fw-medium border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                       required>
                            </div>

                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg">Check In</button>
                        @else
                            <div class="mb-4 text-gray-900">
                                <h3 class="text-lg font-bold mb-2">Last Checked
                                    In: {{ $last_activity['check_in'] ?? 'Check in time not found.' }}</h3>
                            </div>
                            <div class="mb-4">
                                <label for="datetime" class="block text-sm font-medium text-gray-700">Select Date and
                                    Time</label>
                                <input type="text" id="datetime" name="checkout"
                                       class="mt-1 block fw-medium border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                       required>
                            </div>
                            <div>
                                <label for="description"
                                       class="block text-sm font-medium text-gray-700">Remarks: </label>
                                <textarea name="description" rows="4"
                                          class="border border-gray-300 rounded-lg px-4 py-2 focus:ring focus:outline-none w-full max-w-sm"></textarea>
                            </div>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg">Check Out</button>
                        @endif

                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            flatpickr("#datetime", {
                enableTime: true,        // Enable time selection
                dateFormat: "Y-m-d H:i", // Set format to "Year-Month-Day Hour:Minute"
                time_24hr: true,         // Use 24-hour format
                defaultDate: new Date(), // Set the default date to the current date and time
            });

            const weeklyChartData = @json($weeklyData);
            const ctx = document.getElementById('myBarChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: weeklyChartData.labels,
                    datasets: [{
                        label: 'Weekly Working Log',
                        data: weeklyChartData.data,
                        backgroundColor: 'rgba(75, 192, 192, 0.5)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1,
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                        }
                    },
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'Days'
                            }
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'Hour'
                            },
                            beginAtZero: true
                        }
                    }
                }
            });

            const monthlyChartData = @json($monthlyData);
            const ctx1 = document.getElementById('myLineChart').getContext('2d');
            new Chart(ctx1, {
                type: 'line',
                data: {
                    labels: monthlyChartData.labels,
                    datasets: [{
                        label: 'Monthly Working Hour Log',
                        data: monthlyChartData.data,
                        borderColor: 'rgba(75, 192, 192, 1)',
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderWidth: 2,
                        tension: 0.4, // Curve the line
                        pointStyle: 'circle',
                        pointRadius: 4,
                        pointBackgroundColor: 'rgba(75, 192, 192, 1)'
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                        }
                    },
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'Months',
                            }
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'Hours',
                            },
                            beginAtZero: true
                        }
                    }
                }
            });
        });
    </script>
</x-app-layout>

