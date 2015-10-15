<?php

namespace Taski;

class Taski
{

    const APP_DEF_DIR = "apps/";
    const APP_OUT_DIR = "output/";

    public function app_defined($app)
    {
        return file_exists(self::APP_DEF_DIR . $app);
    }

    public function execute($app, $params, $browserOutput = '')
    {
        if ($this->app_defined($app))
        {
            $now = new \DateTime();
            $nowIso = $now->format('Y-m-d_H-i-s');

            $directory = self::APP_OUT_DIR . $nowIso . '_' . $app . '/';
            mkdir($directory);

            file_put_contents($directory . 'app.txt', $app);
            file_put_contents($directory . 'id.txt', uniqid());

            $commandLine = '"' . realpath(self::APP_DEF_DIR . $app) . '" "' . $params . '">"' . realpath($directory) . '/output.txt"';
            file_put_contents($directory . 'cmd.txt', $commandLine);

            $start = new \DateTime();
            file_put_contents($directory . 'starttime.txt', $start->format('c'));

            $this->close_client_connection_and_continue($browserOutput);
            $this->start_process_async($commandLine, realpath($directory));
            return true;
        }
        else
            return false;
    }

    public function get_tasks()
    {
        $result = array();
        $directories = glob(self::APP_OUT_DIR . '/*' , GLOB_ONLYDIR);

        foreach ($directories as $directory)
        {
            $status = 'running';

            $cmdFilePath = $directory . '/cmd.txt';
            $cmd = '';
            if (file_exists($cmdFilePath))
                $cmd = file_get_contents($cmdFilePath);

            $outputFilePath = $directory . '/output.txt';
            $output = '';
            if (file_exists($outputFilePath))
                $output = file_get_contents($outputFilePath);

            $startTimeFilePath = $directory . '/starttime.txt';
            $startTime = '';
            if (file_exists($startTimeFilePath))
                $startTime = file_get_contents($startTimeFilePath);

            $endTimeFilePath = $directory . '/endtime.txt';
            $endTime = '';
            if (file_exists($endTimeFilePath))
            {
                $endTime = file_get_contents($endTimeFilePath);
                $status = 'ready';
            }

            $appFilePath = $directory . '/app.txt';
            $app = '';
            if (file_exists($appFilePath))
                $app = file_get_contents($appFilePath);

            $idFilePath = $directory . '/id.txt';
            $id = '';
            if (file_exists($idFilePath))
                $id = file_get_contents($idFilePath);

            $exitCodeFilePath = $directory . '/exitcode.txt';
            $exitCode = '';
            if (file_exists($exitCodeFilePath))
                $exitCode = file_get_contents($exitCodeFilePath);

            $result[] = array(
                'cmd' => $cmd,
                'output' => $output,
                'startTime' => $startTime,
                'endTime' => $endTime,
                'app' => $app,
                'id' => $id,
                'status' => $status,
                'exitCode' => $exitCode
                );
        }
        
        return array_reverse($result);
    }

    public function get_apps()
    {
        $result = array();
        if ($handle = opendir(self::APP_DEF_DIR))
        {
            while (($item = readdir($handle)) !== false)
            {
                if ($item != '.' && $item != '..' && $item != '_ReadMe.txt')
                    $result[] = $item;
            }
            closedir($handle);
        }
        return $result;
    }

    private function start_process_async($commandLine, $executingDirectory = '')
    { 
        if (empty($executingDirectory))
            $executingDirectory = getcwd();

        if (substr(php_uname(), 0, 7) == 'Windows')
            pclose(popen('start /D "' . $executingDirectory . '" "" cmd /c "' . $commandLine . '"', 'r'));  
        else
            exec('cd "' . $executingDirectory . '" && ' . $commandLine . " > /dev/null &");   
    }

    private function close_client_connection_and_continue ($browserOutput)
    {
        ob_end_clean();
        header('Connection: close');
        ignore_user_abort(true);
        ob_start();
        echo($browserOutput);
        $size = ob_get_length();
        header('Content-Length: ' . $size);
        ob_end_flush();
        ob_flush();
        flush();
    }
}
