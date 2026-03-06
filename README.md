# GitHub Core

> Библиотека и каталог skills для работы с GitHub API

Этот пакет содержит GitHubClient — общий API-клиент для всех skills GitHub:
- Авторизация через Personal Access Token
- Запросы к GitHub API
- Экспорт в CSV/Markdown

## Skills на основе этого пакета

| Skill | Описание | Репозиторий |
|-------|----------|-------------|
| github-traffic | Просмотры и клоны репозиториев | [github.com/prikotov/github-traffic](https://github.com/prikotov/github-traffic) |
| github-referrers | Источники трафика GitHub | [github.com/prikotov/github-referrers](https://github.com/prikotov/github-referrers) |
| github-paths | Популярные страницы репозиториев | [github.com/prikotov/github-paths](https://github.com/prikotov/github-paths) |
| github-stars | Динамика звёзд | [github.com/prikotov/github-stars](https://github.com/prikotov/github-stars) |

## Установка

Skills совместимы с различными AI-агентами. Примеры ниже даны для OpenCode — для других инструментов смотрите их документацию по установке skills.

### 1. Установите core

```bash
git clone https://github.com/prikotov/github-core.git .opencode/skills/github-core
```

### 2. Создайте GitHub Personal Access Token

1. Перейдите на https://github.com/settings/tokens
2. Нажмите **Generate new token (classic)**
3. Заполните:
   - **Note**: `GitHub Stats`
   - **Expiration**: No expiration (или по необходимости)
   - **Scopes**: 
     - `public_repo` — для публичных репозиториев
     - `repo` — для приватных репозиториев
4. Скопируйте токен

### 3. Создайте конфигурацию

```bash
cp .opencode/skills/github-core/github_config.example.json ./github_config.json
```

Заполните:
```json
{
    "token": "ghp_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx",
    "default_repo": "username/repository",
    "repos": {
        "my-project": "username/my-project",
        "another": "username/another-repo"
    }
}
```

Параметр `default_repo` определяет репозиторий, который используется если не указан `--repo`.

### 4. Установите нужные skills

```bash
git clone https://github.com/prikotov/github-traffic.git .opencode/skills/github-traffic
git clone https://github.com/prikotov/github-referrers.git .opencode/skills/github-referrers
git clone https://github.com/prikotov/github-paths.git .opencode/skills/github-paths
git clone https://github.com/prikotov/github-stars.git .opencode/skills/github-stars
```

## Структура

```
your-project/
├── github_config.json          # Конфиг (создаётся вручную в корне проекта)
├── github_reports/              # Создаётся автоматически при запуске отчёта
│   └── YYYY-MM-DD/              # Папка с отчётами за день
│       └── github_*             # Файлы отчётов
└── .opencode/skills/
    ├── github-core/             # Библиотека
    ├── github-traffic/          # Просмотры и клоны
    ├── github-referrers/        # Источники трафика
    ├── github-paths/            # Популярные страницы
    └── github-stars/            # Динамика звёзд
```

## Безопасность

GitHubClient автоматически защищает конфиденциальные данные от случайной публикации в git. При первом запуске он проверяет `.gitignore` и напоминает добавить недостающие записи.

Защищаемые файлы:
- `github_config.json` — токен и список репозиториев
- `github_reports/` — папка с отчётами

## Создание нового skill

1. Создайте репозиторий `github-XXX`
2. Подключите GitHubClient:
```php
<?php
require_once __DIR__ . '/../github-core/GitHubClient.php';

GitHubClient::checkGitignore();
$config = GitHubClient::loadConfig();

$client = new GitHubClient(
    $config['token'],
    $config['default_repo']
);

// Ваш код...
```

### Пример использования GitHubClient API

Ниже — полный пример skill, который запрашивает просмотры репозитория и сохраняет отчёт.

```php
<?php
require_once __DIR__ . '/../github-core/GitHubClient.php';

// 1. Проверка .gitignore и загрузка конфига
GitHubClient::checkGitignore();
$config = GitHubClient::loadConfig();

// 2. Получение репозитория (можно указать алиас или использовать default)
$repoFullName = GitHubClient::getRepoFromConfig($config, 'my-project');

// 3. Создание клиента
$client = new GitHubClient(
    $config['token'],
    $repoFullName,
    'my-project'  // опционально, для вывода в логах
);

// 4. Запрос к GitHub API
// Документация: https://docs.github.com/en/rest
$data = $client->request("/repos/{$client->getRepo()}/traffic/views");

// 5. Преобразование ответа в плоский массив
$rows = [];
foreach ($data['views'] ?? [] as $item) {
    $rows[] = [
        'date' => substr($item['timestamp'], 0, 10),
        'views' => $item['count'],
        'unique' => $item['uniques']
    ];
}

// 6. Сохранение отчёта в CSV и Markdown
$reportDir = GitHubClient::createReportDir();
$timestamp = GitHubClient::getFileTimestamp();
GitHubClient::saveCsv($rows, "$reportDir/views_$timestamp.csv");
GitHubClient::saveMarkdown($rows, "$reportDir/views_$timestamp.md", 'Просмотры репозитория');
```

### Выбор репозитория

Все skills поддерживают параметр `--repo`:

```bash
# Использовать default_repo из конфига
php .opencode/skills/github-traffic/traffic.php

# Указать конкретный репозиторий по алиасу
php .opencode/skills/github-traffic/traffic.php --repo my-project

# Указать репозиторий полностью
php .opencode/skills/github-traffic/traffic.php --repo username/repo
```

## Требования

- PHP 7.4+
- Расширение cURL

---

## Пример работы в AI-агенте

**Пользователь:**
> Проанализируй трафик на моих репозиториях за последние 14 дней. Какие репозитории популярнее? Откуда приходит трафик?

**Агент (рассуждения):**
> Загружаю skill для анализа трафика GitHub. Запускаю запросы параллельно для всех репозиториев: просмотры, клоны, источники трафика. Анализирую метрики: уникальные посетители, конверсия в клоны, источники...

**Агент (ответ):**

---

**Сводка по репозиториям (14 дней)**

| Репозиторий | Просмотров | Уник. | Клонов | Уник. | Звёзд |
|-------------|------------|-------|--------|-------|-------|
| **task-agents-playbook** | 1078 | 163 | 182 | 82 | 10 |
| yandex-metrika-core | 108 | 5 | 239 | 142 | 0 |
| yandex-metrika-search | 52 | 3 | 154 | 102 | 0 |
| google-trends | 40 | 1 | 82 | 51 | 0 |

**Источники трафика (playbook)**

| Источник | Просмотров | Уникальных |
|----------|------------|------------|
| github.com | 225 | 9 |
| prikotov.pro | 187 | 31 |
| task.ai-aid.pro | 28 | 2 |
| yandex.ru | 13 | 3 |
| Google | 5 | 5 |

**Выводы**

*Популярность:*
- **task-agents-playbook** — главный магнит (1078 просмотров, 163 уникальных)
- Metrika-библиотеки клонируют больше, чем смотрят (находят через поиск/зависимости)

*Источники:*
- Блог prikotov.pro — основной драйвер трафика (31 уникальный посетитель)
- GitHub internal traffic — второй по объёму (9 уникальных)
- task.ai-aid.pro — даёт целевой трафик (2 уникальных)

*Конверсия:*
- Playbook: 50% зрителей клонируют (82/163)
- Metrika-библиотеки: 200-300% конверсия (находят не через browsing)

---

> Постановка задач, архитектура, ревью — [Dmitry Prikotov](https://prikotov.pro/), реализация — GLM-5 в [OpenCode](https://opencode.ai)
