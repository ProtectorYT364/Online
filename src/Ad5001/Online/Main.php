<?php
namespace Ad5001\Online; 
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\network\upnp\UPnP;
use pocketmine\Server;
use pocketmine\Player;


class Main extends PluginBase{
public function onEnable(){
$this->saveDefaultConfig();
if(!file_exists($this->getDataFolder() . "index.html")) {
    file_put_contents($this->getDataFolder() . "index.html", $this->getResource("index.html"));
}
if(!stream_resolve_include_path("router.php")) {
    file_put_contents($this->getDataFolder() . "router.php", $this->getResource("handler.php"));
}
if(!file_exists($this->getDataFolder() . "404.html")) {
    file_put_contents($this->getDataFolder() . "404.html", $this->getResource("404.html"));
}
if(!file_exists($this->getDataFolder() . "403.html")) {
    file_put_contents($this->getDataFolder() . "403.html", $this->getResource("403.html"));
}
set_time_limit(0);

$this->port = $this->getConfig()->get("port");

$this->getServer()->getScheduler()->scheduleAsyncTask(new execTask($this->getServer()->getFilePath()));
// UPnP::PortForward($port); \\\\ Beta for Windows
}

public function onDisable() {
    if($this->getConfig()->get("KillOnShutdown") !== "false") {
        switch(true) {
            case stristr(PHP_OS, "WIN"):
            exec('FOR /F "tokens=4 delims= " %P IN (\'netstat -a -n -o ^| findstr :'. $this->port .'\') DO @ECHO TaskKill.exe /PID %P');
            break;
            case stristr(PHP_OS, "DAR") or stristr(PHP_OS, "LINUX"):
            shell_exec("kill -kill `lsof -t -i tcp:$this->port`");
            break;
        }
    }
    
}
}

class execTask extends \pocketmine\scheduler\AsyncTask {

    public function __construct(string $path) {
        $this->path = $path;
    }

    public function onRun() {
        $address = '0.0.0.0';
        $port = yaml_parse(file_get_contents("plugins\\Online\\config.yml"))["port"];
        // shell_exec("cd plugins/Online");
        switch(true) {
            case stristr(PHP_OS, "WIN"):
            // echo '"%CD%\\bin\\php\\php.exe -t %CD%\\plugins\\Online -n -d include_path=\'%CD%\\plugins\\Online\\\' -S ' . $address . ":" . $port . ' -f %CD%\\plugins\\Online\\router.php"';
            shell_exec('start "Online Listener" cmd /c "%CD%\\bin\\php\\php.exe -t %CD%\\plugins\\Online -n -d include_path=\'%CD%\\plugins\\Online\\\' -d extension=\'%CD%\\bin\\php\\ext\\php_yaml.dll\' -S ' . $address . ":" . $port . ' router.php"');
            break;
            case stristr(PHP_OS, "DAR"):
            shell_exec('open -a Terminal "' . $this->path . "bin\\php\\php.exe -t " . $this->path . "plugins\\Online -n -d include_path=\'" . $this->path . "plugins\\Online\\\' -d extension=\'" . $this->path . "bin\\php\\ext\\php_yaml.dll\' -S " . $address . ":" . $port . ' router.php"');
            break;
            case stristr(PHP_OS, "LINUX"):
            shell_exec('gnome-terminal -e "' . $this->path . "bin\\php\\php.exe -t " . $this->path . "plugins\\Online -n -d include_path=\'" . $this->path . "plugins\\Online\\\' -d extension=\'" . $this->path . "bin\\php\\ext\\php_yaml.dll\' -S " . $address . ":" . $port . ' router.php"');
            break;
        }
    }
}