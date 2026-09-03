document.addEventListener("DOMContentLoaded", function () {
    renderPieChartFromInputs('categoryLabels', 'categoryData', 'categoryChart', 'Recipes by Category');
    renderBarChartFromInputs('userLabels', 'userData', 'userChart', 'Top Users by Uploads', 'Uploads');
    renderBarChartFromInputs('favouriteLabels', 'favouriteData', 'likeChart', 'Top Liked Recipes', 'Likes');
    renderBarChartFromInputs('bookmarkLabels', 'bookmarkData', 'bookmarkChart', 'Top Bookmarked Recipes', 'Bookmarks');
});

function getChartData(labelsId, dataId) {
    const labelsInput = document.getElementById(labelsId);
    const dataInput = document.getElementById(dataId);

    if (!labelsInput || !dataInput) {
        console.warn(`Chart data not found in DOM for ${labelsId} or ${dataId}.`);
        return { labels: [], data: [] };
    }

    let labels = [];
    let data = [];

    try {
        labels = labelsInput.value ? JSON.parse(labelsInput.value) : [];
        data = dataInput.value ? JSON.parse(dataInput.value) : [];
    } catch (e) {
        console.error(`Invalid JSON in hidden chart inputs for ${labelsId}/${dataId}`, e);
        return { labels: [], data: [] };
    }

    return { labels, data };
}

function renderPieChartFromInputs(labelsId, dataId, canvasId, chartLabel) {
    const { labels, data } = getChartData(labelsId, dataId);

    if (!labels.length || !data.length) {
        console.warn(`No data to display for pie chart (${canvasId}).`);
        return;
    }

    const ctx = document.getElementById(canvasId);
    if (!ctx) {
        console.warn(`Canvas element for pie chart (${canvasId}) not found.`);
        return;
    }

    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: labels,
            datasets: [{
                label: chartLabel,
                data: data,
                backgroundColor: [
                    '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b',
                    '#858796', '#fd7e14', '#20c997', '#6f42c1', '#e83e8c'
                ],
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'left'
                }
            },
            maintainAspectRatio: false
        }
    });
}

function renderBarChartFromInputs(labelsId, dataId, canvasId, chartLabel, yAxisLabel) {
    const { labels, data } = getChartData(labelsId, dataId);

    if (!labels.length || !data.length) {
        console.warn(`No data to display for bar chart (${canvasId}).`);
        return;
    }

    const ctx = document.getElementById(canvasId);
    if (!ctx) {
        console.warn(`Canvas element for bar chart (${canvasId}) not found.`);
        return;
    }

    // Color palette for bars
    const colors = [
        '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b',
        '#858796', '#fd7e14', '#20c997', '#6f42c1', '#e83e8c'
    ];

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: chartLabel,
                data: data,
                backgroundColor: colors.slice(0, data.length),  // Assign colors per bar
                borderColor: colors.slice(0, data.length),
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        callback: function(value) {
                            return Math.floor(value) === value ? value : '';
                        }
                    },
                    title: {
                        display: false,
                    }
                },
                x: {
                    title: {
                        display: false,
                    }
                }
            },
            maintainAspectRatio: false
        }
    });
}