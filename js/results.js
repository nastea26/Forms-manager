const scriptTag = document.querySelector('script[data-questions]');
const questionsData = JSON.parse(scriptTag.dataset.questions);

const questions = Object.keys(questionsData);
let currentSlide = 0;

const slider = document.getElementById("slider");
const prevButton = document.getElementById("prevButton");
const nextButton = document.getElementById("nextButton");

// Statistical Functions
// Statistical Functions
function calculateMean(responses) {
    const total = Object.entries(responses).reduce((sum, [key, value]) => sum + key * value, 0);
    const count = Object.values(responses).reduce((a, b) => a + b, 0);
    const mean = count > 0 ? total / count : NaN;
    return isNaN(mean) ? "Unavailable for the current question" : mean.toFixed(2);
}

function calculateMode(responses) {
    const max = Math.max(...Object.values(responses));
    const modes = Object.entries(responses)
        .filter(([_, value]) => value === max)
        .map(([key]) => key);
    return modes.length > 0 ? modes.join(", ") : "Unavailable for the current question";
}

function calculateRange(responses) {
    const values = Object.keys(responses).map(Number);
    if (values.length > 0) {
        const min = Math.min(...values);
        const max = Math.max(...values);
        const range = max - min;
        return isNaN(range) ? "Unavailable for the current question" : range.toFixed(2);
    }
    return "Unavailable for the current question";
}

function calculateMedian(responses) {
    const values = [];
    for (const [key, value] of Object.entries(responses)) {
        for (let i = 0; i < value; i++) {
            values.push(Number(key));
        }
    }
    if (values.length === 0) return "Unavailable for the current question";
    values.sort((a, b) => a - b);
    const mid = Math.floor(values.length / 2);
    const median = values.length % 2 !== 0 ? values[mid] : (values[mid - 1] + values[mid]) / 2;
    return isNaN(median) ? "Unavailable for the current question" : median.toFixed(2);
}

function calculateStandardDeviation(responses) {
    const values = Object.entries(responses);
    const total = values.reduce((sum, [key, value]) => sum + key * value, 0);
    const count = values.reduce((sum, [_, value]) => sum + value, 0);
    const mean = count > 0 ? total / count : NaN;

    if (mean !== null && count > 1) {
        const variance = values.reduce((sum, [key, value]) => sum + value * Math.pow(key - mean, 2), 0) / count;
        const stdDev = Math.sqrt(variance);
        return isNaN(stdDev) ? "Unavailable for the current question" : stdDev.toFixed(2);
    }
    return "Unavailable for the current question";
}
function calculateMin(responses) {
    const values = Object.keys(responses).map(Number);
    const min = Math.min(...values);
    return isFinite(min) ? min : "Unavailable for the current question";
}

function calculateMax(responses) {
    const values = Object.keys(responses).map(Number);
    const max = Math.max(...values);
    return isFinite(max) ? max : "Unavailable for the current question";
}

function calculateSkewness(responses) {
    const values = Object.entries(responses);
    const count = values.reduce((sum, [_, value]) => sum + value, 0);
    const mean = calculateMean(responses);
    const stdDev = calculateStandardDeviation(responses);

    if (count > 0 && !isNaN(stdDev) && stdDev > 0) {
        const skewness = values.reduce((sum, [key, value]) => {
            return sum + value * Math.pow((key - mean), 3);
        }, 0) / (count * Math.pow(stdDev, 3));
        return isNaN(skewness) ? "Unavailable for the current question" : skewness.toFixed(2);
    }
    return "Unavailable for the current question";
}

function calculateKurtosis(responses) {
    const values = Object.entries(responses);
    const count = values.reduce((sum, [_, value]) => sum + value, 0);
    const mean = calculateMean(responses);
    const stdDev = calculateStandardDeviation(responses);

    if (count > 0 && !isNaN(stdDev) && stdDev > 0) {
        const kurtosis = values.reduce((sum, [key, value]) => {
            return sum + value * Math.pow((key - mean), 4);
        }, 0) / (count * Math.pow(stdDev, 4)) - 3; // Excess Kurtosis
        return isNaN(kurtosis) ? "Unavailable for the current question" : kurtosis.toFixed(2);
    }
    return "Unavailable for the current question";
}


function createTooltip(content) {
    return `
        <span class="tooltip-icon" role="tooltip" aria-label="Tooltip">
            ?
            <span class="tooltip-content">${content}</span>
        </span>
    `;
}

function renderSlide() {
    slider.innerHTML = ""; // Clear previous content

    if (currentSlide < questions.length) {
        const question = questions[currentSlide];
        const data = questionsData[question];
        const responses = data.responses;

        const slide = document.createElement("div");
        slide.classList.add("question-slide", "active");

        if (Array.isArray(responses)) {
            // Handle free-text responses
            slide.innerHTML = `
                <h2>${question}</h2>
                <ul>${responses.map(response => `<li>${response}</li>`).join("")}</ul>
            `;
        } else {
            // Handle option-based responses
            const mean = calculateMean(responses);
            const mode = calculateMode(responses);
            const range = calculateRange(responses);
            const median = calculateMedian(responses);
            const stdDev = calculateStandardDeviation(responses);
            const min = calculateMin(responses);
            const max = calculateMax(responses);
            const skewness = calculateSkewness(responses);
            const kurtosis = calculateKurtosis(responses);


            const optionsHtml = Object.entries(responses)
                .map(([option, count]) => `<li>${option}: ${count} response(s)</li>`)
                .join("");

                slide.innerHTML = `
                <h2>${question}</h2>
                <div class="chart-container">
                    <canvas id="barChart-${currentSlide}" style="max-width: 600px; margin: 10px auto;"></canvas>
                    <canvas id="pieChart-${currentSlide}" style="max-width: 600px; margin: 10px auto;"></canvas>
                </div>
                <ul>${optionsHtml}</ul>
                <p><strong>Min:</strong> ${min} ${createTooltip("The smallest response value recorded.")}</p>
                <p><strong>Max:</strong> ${max} ${createTooltip("The largest response value recorded.")}</p>
                <p><strong>Median:</strong> ${median} ${createTooltip("The middle value when responses are ordered.")}</p>
                <p><strong>Mean:</strong> ${mean} ${createTooltip("The average value of the responses.")}</p>
                <p><strong>Mode:</strong> ${mode} ${createTooltip("The most frequently occurring response.")}</p>
                <p><strong>Range:</strong> ${range} ${createTooltip("The difference between the maximum and minimum values.")}</p>
                <p><strong>Standard Deviation:</strong> ${stdDev} ${createTooltip("The spread of responses around the mean.")}</p>
                <p><strong>Skewness:</strong> ${skewness} ${createTooltip("The asymmetry of the response distribution.")}</p>
                <p><strong>Kurtosis:</strong> ${kurtosis} ${createTooltip("The sharpness of the peak of the response distribution.")}</p>
            `;
        }

        slider.appendChild(slide);

        // Render Bar Chart
        if (!Array.isArray(responses)) {
            const ctxBar = document.getElementById(`barChart-${currentSlide}`).getContext("2d");
            const labels = Object.keys(responses);
            const dataPoints = Object.values(responses);

            new Chart(ctxBar, {
                type: "bar",
                data: {
                    labels,
                    datasets: [{
                        label: "Number of Responses",
                        data: dataPoints,
                        backgroundColor: "rgba(54, 162, 235, 0.2)",
                        borderColor: "rgba(54, 162, 235, 1)",
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Render Pie Chart
            const ctxPie = document.getElementById(`pieChart-${currentSlide}`).getContext("2d");
            new Chart(ctxPie, {
                type: "pie",
                data: {
                    labels,
                    datasets: [{
                        data: dataPoints,
                        backgroundColor: [
                            "#FF6384", "#36A2EB", "#FFCE56", "#4BC0C0", "#9966FF", "#FF9F40"
                        ],
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true
                }
            });
        }
    } else {
        // Display summary chart
        const slide = document.createElement("div");
        slide.classList.add("chart-slide", "active");
        slide.innerHTML = '<canvas id="summaryChart"></canvas>';
        slider.appendChild(slide);

        const labels = questions;
        const data = labels.map(question => {
            const answers = questionsData[question].responses;
            return Array.isArray(answers) ?
                answers.length :
                Object.values(answers).reduce((a, b) => a + b, 0);
        });

        const ctx = document.getElementById("summaryChart").getContext("2d");
        new Chart(ctx, {
            type: "bar",
            data: {
                labels,
                datasets: [{
                    label: "Total Responses per Question",
                    data,
                    backgroundColor: "rgba(75, 192, 192, 0.2)",
                    borderColor: "rgba(75, 192, 192, 1)",
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    prevButton.disabled = currentSlide === 0;
    nextButton.disabled = currentSlide >= questions.length;
}

prevButton.addEventListener("click", () => {
    if (currentSlide > 0) {
        currentSlide--;
        renderSlide();
    }
});

nextButton.addEventListener("click", () => {
    if (currentSlide < questions.length) {
        currentSlide++;
        renderSlide();
    }
});

// Initial render
renderSlide();
