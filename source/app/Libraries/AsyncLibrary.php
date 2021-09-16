<?php 
namespace App\Libraries;
 
class AsyncLibrary
{
    /**
     * Run a task in background.
     *
     * @param string $className
     * @param string $fnName
     * @param array|null $params
     */
    public static function do_in_background(string $className, string $fnName, ?array $params=null): void
    {
        $command = sprintf(
            'php %sindex.php %s %s',
            FCPATH,
            $className,
            $fnName,
        );
        if ($params) {
            foreach ($params as $param) {
                $command .= ' ' . escapeshellarg($param);
            }
        }

        if(!self::execute_task($command)) {
            $instance = new $className();
            $instance->$fnName(...array_values($params));
        }

    }

    /**
     * Execute a task with up to 3 attempts.
     *
     * @param string $command
     * @return bool
     */
    public static function execute_task(string $command): bool
    {
        static $count = 0;
        $count++;
        try {
            $output = system($command, $result_code);
            if ($result_code || $output) {
                if ($count < 3) {
                    self::execute_task($command);
                } else {
                    log_message('error', sprintf('system() returned %u with output %1$s', $result_code, $output));
                    return false;
                }
            }
            if ($result_code === 0)
                return true;
            return false;
        } catch (\Exception $e) {
            if ($count <= 3) {
                self::execute_task($command);
            } else {
                log_message('error', 'An exception happened');
                return false;
            }
        }
    }
 
    /**
     * Run a task in background.
     */
    function do_in_background_old($url, $params)
    {
        $post_string = http_build_query($params);
        $parts = parse_url($url);
            $errno = 0;
        $errstr = "";
 
       //Use SSL & port 443 for secure servers
       //Use otherwise for localhost and non-secure servers
       //For secure server
        //$fp = fsockopen('ssl://' . $parts['host'], isset($parts['port']) ? $parts['port'] : 443, $errno, $errstr, 30);

        //For localhost and un-secure server
       $fp = fsockopen($parts['host'], isset($parts['port']) ? $parts['port'] : 80, $errno, $errstr, 30);
        if(!$fp)
        {
            echo "Something Went Wrong";   
        }
        $out = "POST ".$parts['path']." HTTP/1.1\r\n";
        $out.= "Host: ".$parts['host']."\r\n";
        $out.= "Content-Type: application/x-www-form-urlencoded\r\n";
        $out.= "Content-Length: ".strlen($post_string)."\r\n";
        $out.= "Connection: Close\r\n\r\n";
        if (isset($post_string)) $out.= $post_string;
        fwrite($fp, $out);
        fclose($fp);
    }
}
