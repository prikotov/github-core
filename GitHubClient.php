<?php

class GitHubClient
{
    private string $token;
    private string $repo;
    private string $repoAlias;
    private string $apiBase = 'https://api.github.com';
    
    public function __construct(string $token, string $repo, string $repoAlias = null)
    {
        $this->token = $token;
        $this->repo = $repo;
        $this->repoAlias = $repoAlias ?? $repo;
    }
    
    public function getRepo(): string
    {
        return $this->repo;
    }
    
    public function getRepoAlias(): string
    {
        return $this->repoAlias;
    }
    
    public function request(string $endpoint, array $params = []): array
    {
        $url = $this->apiBase . $endpoint;
        
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: token ' . $this->token,
                'Accept: application/vnd.github.v3+json',
                'User-Agent: GitHub-Traffic-Stats'
            ],
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_TIMEOUT => 30
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new Exception("cURL Error: $error");
        }
        
        if ($httpCode === 401) {
            throw new Exception("Ошибка авторизации. Проверьте GitHub токен.");
        }
        
        if ($httpCode === 403) {
            throw new Exception("Доступ запрещён. Возможно, превышен лимит запросов или недостаточно прав.");
        }
        
        if ($httpCode === 404) {
            throw new Exception("Ресурс не найден: $endpoint");
        }
        
        $data = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("JSON Error: " . json_last_error_msg());
        }
        
        return $data;
    }
    
    public static function loadConfig(): array
    {
        $configFile = __DIR__ . '/config.json';
        
        if (!file_exists($configFile)) {
            throw new Exception(
                "Файл конфигурации не найден.\n" .
                "Создайте .opencode/skills/github-core/config.json:\n" .
                '{' . "\n" .
                '  "token": "ghp_xxx",' . "\n" .
                '  "default_repo": "username/repo"' . "\n" .
                '}'
            );
        }
        
        $config = json_decode(file_get_contents($configFile), true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Ошибка в config.json: " . json_last_error_msg());
        }
        
        return $config;
    }
    
    public static function getRepoFromConfig(array $config, ?string $alias): string
    {
        if ($alias === null) {
            return $config['default_repo'] ?? throw new Exception("Не указан default_repo в config.json");
        }
        
        if (isset($config['repos'][$alias])) {
            return $config['repos'][$alias];
        }
        
        if (strpos($alias, '/') !== false) {
            return $alias;
        }
        
        throw new Exception("Репозиторий '$alias' не найден в config.json");
    }
    
    public static function checkGitignore(): void
    {
        $gitignorePath = dirname(__DIR__, 4) . '/.gitignore';
        $configPattern = '.opencode/skills/github-core/config.json';
        
        if (!file_exists($gitignorePath)) {
            return;
        }
        
        $content = file_get_contents($gitignorePath);
        
        if (strpos($content, $configPattern) === false) {
            echo "\n  ⚠️  Внимание: добавьте в .gitignore:\n";
            echo "      $configPattern\n\n";
        }
    }
    
    public static function createReportDir(): string
    {
        $baseDir = dirname(__DIR__, 4) . '/github_reports';
        
        if (!is_dir($baseDir)) {
            mkdir($baseDir, 0755, true);
        }
        
        $reportDir = $baseDir . '/' . date('Y-m-d');
        
        if (!is_dir($reportDir)) {
            mkdir($reportDir, 0755, true);
        }
        
        return $reportDir;
    }
    
    public static function getFileTimestamp(): string
    {
        return date('Y-m-d_H-i-s');
    }
    
    public static function saveCsv(array $data, string $filepath): void
    {
        if (empty($data)) {
            file_put_contents($filepath, '');
            return;
        }
        
        $fp = fopen($filepath, 'w');
        
        fputcsv($fp, array_keys($data[0]));
        
        foreach ($data as $row) {
            fputcsv($fp, $row);
        }
        
        fclose($fp);
    }
    
    public static function saveMarkdown(array $data, string $filepath, string $title, string $period = null): void
    {
        $md = "# $title\n\n";
        
        if ($period) {
            $md .= "**Период:** $period\n\n";
        }
        
        if (empty($data)) {
            $md .= "*Нет данных*\n";
            file_put_contents($filepath, $md);
            return;
        }
        
        $headers = array_keys($data[0]);
        $md .= "| " . implode(" | ", $headers) . " |\n";
        $md .= "| " . implode(" | ", array_fill(0, count($headers), '---')) . " |\n";
        
        foreach ($data as $row) {
            $md .= "| " . implode(" | ", array_values($row)) . " |\n";
        }
        
        $md .= "\n---\n";
        $md .= "*Сгенерировано: " . date('Y-m-d H:i:s') . "*\n";
        
        file_put_contents($filepath, $md);
    }
}
