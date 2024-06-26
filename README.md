# Тестовое задание для WordPress Fullstack Developer

## Задача:

> <img width="200" src="https://raw.githubusercontent.com/wowvendor/wowvendor-junior-test/97bf30dc6a091261bd6fc6409e9c8e2791c3d745/images/donut.svg">\
> \
> Для выполнения тестового задания необходимо склонировать данный репозиторий на локальную машину. \
> В качестве результата задания принимается ссылка на публичный репозиторий в Github
> 
> ## На выполнение тестового задания отводится 2 дня
> 
> ## Дано:
> 
> Приложение мини-игра. Целью игры является добежать до финиша, не столкнувшись с препятствием.\
> В приложении реализованы основные методы управления персонажем: ```run, stop, jump```;
> 
> ## Задачи:
> 1. На базе приложения создать WordPress-плагин;
> 2. В плагине добавить возможность отображать мини-игру на определённой странице (page) или странице записи (post) при помощи переключателя;
> 3. При открытии страницы, поверх мини-игры появляется всплывающее окно с предложением начать игру;
> 4. Пончик проходит игру в автоматическом режиме после нажатия на кнопку старт. После окончания игры всплывает окно с результатом забега и предложением попробовать ещё раз. Пончик должен проходить игру успешно (то есть перепрыгивать камень и доходить до финиша);
> 5. Написать PHP скрипт, принимающий результаты забега и записывающий их в базу данных WordPress. Данные записываются в целом для игры, а не для отдельной страницы;
> 6. Отправлять результаты забега каждый раз, когда персонаж доходит до финиша или спотыкается о препятствие;
> 7. Под блоком с игрой выводится статистика игр, упорядоченная по времени.
> 
> ### Результаты забега:
> 
> 1. Позиция препятствия;
> 2. Время забега;
> 3. Дистанция на которой персонаж совершил прыжок;
> 4. Размер препятствия;
> 5. Результат забега (Успех или Провал).
> 
> 
> ### Получение данных и методы управления:
> 
> #### Управление персонажем:
> ```js
>     window.character.run();
> window.character.stop();
> window.character.jump(); 
> ```
> 
> #### Публичные свойства:
> ```js
>  window.terrain.rockPosition;
>  window.terrain.rockSize;
>  window.character.characterPosition;
> ```
> ### Результаты работы:
> Публичный репозиторий Github, содержащий:
> 1. Репозиторий с вашим плагином.

## Реализация:

<img width="100%" src="demo.gif" alt="Демонстрация реализации в GIF">