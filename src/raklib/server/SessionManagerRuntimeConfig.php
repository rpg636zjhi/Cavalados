<?php

namespace raklib\server;

class SessionManagerRuntimeConfig
{
    public static function load($configPath)
    {
        $result = [
            'mode' => 'anti_ddos',
            'protection_enabled' => true,
            'reload_interval_seconds' => 1,
        ];

        if (!is_string($configPath) || $configPath === '') {
            $configPath = __DIR__ . DIRECTORY_SEPARATOR . 'session_manager_hot_reload.json';
        }

        if (!is_file($configPath)) {
            return $result;
        }

        $content = @file_get_contents($configPath);
        if ($content === false) {
            return $result;
        }

        $data = json_decode($content, true);
        if (!is_array($data)) {
            return $result;
        }

        $mode = isset($data['mode']) ? strtolower((string) $data['mode']) : '';
        $protectionEnabled = isset($data['protection_enabled']) ? (bool) $data['protection_enabled'] : true;

        if ($mode === 'light' || $mode === 'leve' || $mode === 'lite') {
            $protectionEnabled = false;
            $modeName = 'light';
        } elseif ($mode === 'anti_ddos' || $mode === 'anti' || $mode === 'protected') {
            $protectionEnabled = true;
            $modeName = 'anti_ddos';
        } else {
            $modeName = $protectionEnabled ? 'anti_ddos' : 'light';
        }

        $result['mode'] = $modeName;
        $result['protection_enabled'] = $protectionEnabled;
        $result['reload_interval_seconds'] = isset($data['reload_interval_seconds']) ? max(1, (int) $data['reload_interval_seconds']) : 1;

        return $result;
    }
}
