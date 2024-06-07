document.addEventListener("DOMContentLoaded", function () {
    // Инициализация игры
    initializeGame();

    // Добавляем обработчики событий для кнопок управления
    document.addEventListener("keyup", function (event) {
        if (event.code === "Space") {
            window.character.jump();
        } else if (event.code === "KeyS") {
            window.character.stop();
        } else if (event.code === "KeyD") {
            window.character.run();
        }
    });

    // Показать оверлей при загрузке страницы
    const gameOverlay = document.getElementById("game-overlay");
    if (gameOverlay) {
        gameOverlay.style.display = "flex";
    }

    // Добавляем обработчик события для кнопки старта игры
    const startGameButton = document.getElementById("start-game");
    if (startGameButton && gameOverlay) {
        startGameButton.addEventListener("click", function () {
            startGame();
        });
    }

    function initializeGame() {
        window.character = new window.Character();
        window.terrain = new window.Terrain();
    }

    function startGame() {
        initializeGame();

        if (gameOverlay) {
            gameOverlay.style.display = "none";
        }
        startTime = new Date().getTime();
        window.character.run();

        // Автоматическое прохождение игры
        const interval = setInterval(function () {
            const characterPos = parseInt(window.character.donut.style.left);
            const rockPos = parseInt(window.terrain.rock.style.left);
            const rockWidth = parseInt(window.terrain.rock.style.width);
            const jumpThreshold = 120;

            if (characterPos + jumpThreshold >= rockPos && characterPos <= rockPos + rockWidth) {
                window.character.jump();
            }

            if (characterPos >= 1020) {
                clearInterval(interval);
                window.character.stop();
                const endTime = new Date().getTime();
                const runTime = (endTime - startTime) / 1000;
                window.character.isWin = true;
                sendGameResults(window.character, window.terrain, runTime);
                showGameResultOverlay(miniGameAjax.i18n.success, runTime);
            }

            if (window.character.isCollided) {
                clearInterval(interval);
                window.character.stop();
                const endTime = new Date().getTime();
                const runTime = (endTime - startTime) / 1000;
                window.character.isWin = false;
                sendGameResults(window.character, window.terrain, runTime);
                showGameResultOverlay(miniGameAjax.i18n.failure, runTime);
            }
        }, 10);
    }

    // Функция для отправки результатов игры на сервер
    function sendGameResults(character, terrain, runTime) {
        const data = {
            action: "submit_game_results",
            rock_position: terrain.rockPosition,
            run_time: runTime,
            jump_distance: character.characterPosition,
            rock_size: terrain.rockSize,
            run_result: character.isWin ? miniGameAjax.i18n.success : miniGameAjax.i18n.failure,
        };

        fetch(miniGameAjax.ajax_url, {
            method: "POST",
            body: new URLSearchParams(data),
            headers: {
                "Content-Type": "application/x-www-form-urlencoded",
            },
        })
            .then((response) => response.json())
            .then((response) => {
                if (response.success) {
                    console.log("Results submitted successfully");
                    updateResultsTable(response.data);
                } else {
                    console.error("Failed to submit results");
                }
            });
    }

    // Функция для обновления таблицы результатов
    function updateResultsTable(result) {
        let resultTable = document.getElementById("result-table");
        let resultTableBody;

        if (!resultTable) {
            // Создать таблицу если её нет
            resultTable = document.createElement("table");
            resultTable.id = "result-table";
            resultTable.innerHTML = `
                <thead>
                    <tr>
                        <th>${miniGameAjax.i18n.rockPosition}</th>
                        <th>${miniGameAjax.i18n.runTime}</th>
                        <th>${miniGameAjax.i18n.jumpDistance}</th>
                        <th>${miniGameAjax.i18n.rockSize}</th>
                        <th>${miniGameAjax.i18n.runResult}</th>
                    </tr>
                </thead>
                <tbody></tbody>
            `;
            document.getElementById("result-table-container").innerHTML = "";
            document.getElementById("result-table-container").appendChild(resultTable);
        }

        resultTableBody = resultTable.getElementsByTagName("tbody")[0];
        const newRow = resultTableBody.insertRow(0); // Вставить новую строку в начало таблицы
        newRow.insertCell(0).innerText = result.rock_position;
        newRow.insertCell(1).innerText = result.run_time;
        newRow.insertCell(2).innerText = result.jump_distance;
        newRow.insertCell(3).innerText = result.rock_size;
        newRow.insertCell(4).innerText = result.run_result;
    }

    // Функция для отображения оверлея с результатами игры
    function showGameResultOverlay(result, runTime) {
        const overlay = document.createElement("div");
        overlay.id = "game-result-overlay";
        overlay.style.position = "absolute";
        overlay.style.top = "0";
        overlay.style.left = "0";
        overlay.style.width = "100%";
        overlay.style.height = "100%";
        overlay.style.backgroundColor = "rgba(0, 0, 0, 0.5)";
        overlay.style.display = "flex";
        overlay.style.justifyContent = "center";
        overlay.style.alignItems = "center";
        overlay.style.zIndex = "100";

        const resultBox = document.createElement("div");
        resultBox.style.backgroundColor = "#fff";
        resultBox.style.padding = "20px";
        resultBox.style.borderRadius = "5px";
        resultBox.style.textAlign = "center";

        const resultText = document.createElement("h2");
        resultText.innerText = `${miniGameAjax.i18n.gameResult}: ${result}`;
        resultBox.appendChild(resultText);

        const resultTime = document.createElement("p");
        resultTime.innerText = `${miniGameAjax.i18n.yourResult}: ${runTime} s.`;
        resultBox.appendChild(resultTime);

        const retryButton = document.createElement("button");
        retryButton.classList.add("btn");
        retryButton.classList.add("retry-button");
        retryButton.innerText = miniGameAjax.i18n.retry;;
        retryButton.addEventListener("click", function () {
            document.getElementById("app").removeChild(overlay);
            startGame(); // Запускаем игру заново с небольшой задержкой
            
            const countdownContainer = document.createElement('div');
            countdownContainer.id = "countdown-container";
            countdownContainer.style.backgroundColor = "rgba(0, 0, 0, 0.15)";
            countdownContainer.style.position = "absolute";
            countdownContainer.style.top = "0";
            countdownContainer.style.left = "0";
            countdownContainer.style.width = "100%";
            countdownContainer.style.height = "100%";
            countdownContainer.style.display = "flex";
            countdownContainer.style.justifyContent = "center";
            countdownContainer.style.alignItems = "center";
            countdownContainer.style.zIndex = "100";
            countdownContainer.style.textAlign = 'center';
            countdownContainer.style.zIndex = '1000';
            
            
            const countdownElement = document.createElement('p');
            countdownElement.style.padding = '1rem';
            countdownElement.style.background = '#fff';
            countdownElement.style.color = '#222';
            countdownElement.style.borderRadius = '.8rem';
            countdownElement.style.fontSize = '2rem';
            countdownElement.style.fontWeight = 'bold';
            countdownElement.style.textAlign = 'center';
            countdownElement.style.width = '160';
            
            let countdown = 2000;
            countdownElement.innerText = countdown;
            document.getElementById("app").appendChild(countdownContainer);
            document.getElementById("countdown-container").appendChild(countdownElement);

            let intervalId = setInterval(() => {
                countdownElement.innerText = countdown;
                countdown -= 100;
                if (countdown <= 0) {
                    clearInterval(intervalId);
                    document.getElementById("app").removeChild(countdownContainer);

                    // "Старт!"
                    countdownContainer.innerHTML = '';
                    countdownContainer.style.backgroundColor = "transparent";
                    document.getElementById("app").appendChild(countdownContainer);

                    countdownElement.innerText = `${miniGameAjax.i18n.go}!`;
                    countdownElement.classList.add("start-animation");
                    document.getElementById("countdown-container").appendChild(countdownElement);

                    setTimeout(() => {
                        countdownElement.classList.add('fadeout-animation');
                    }, 1000);
                    setTimeout(() => {
                        document.getElementById("app").removeChild(countdownContainer);
                    }, 1500);
                }
            }, 100);

            setTimeout(() => {
                window.character.run(); // Начинаем бег с небольшой задержкой
            }, 2000); // 2 секунды до запуска бега
        });

        resultBox.appendChild(retryButton);
        overlay.appendChild(resultBox);
        document.getElementById("app").appendChild(overlay);
    }

    // Обработчик для кнопки "Очистить результаты"
    const clearResultsButton = document.getElementById("clear-results");
    if (clearResultsButton) {
        clearResultsButton.addEventListener("click", function () {
            if (confirm(miniGameAjax.i18n.confirmClearResults)) {
                fetch(miniGameAjax.ajax_url, {
                    method: "POST",
                    body: new URLSearchParams({
                        action: "clear_game_results"
                    }),
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded",
                    },
                })
                    .then((response) => response.json())
                    .then((response) => {
                        if (response.success) {
                            const resultContainer = document.getElementById("result-table-container");
                            if (resultContainer) {
                                resultContainer.innerHTML = `<p>${miniGameAjax.i18n.noResults}</p>`;
                            }
                            console.log("Results cleared successfully");
                        } else {
                            console.error("Failed to clear results");
                        }
                    });
            }
        });
    }
});