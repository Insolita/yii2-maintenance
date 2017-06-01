<?php
/**
 * Created by solly [23.04.16 14:01]
 */

namespace insolita\maintenance;

use yii\base\BootstrapInterface;
use yii\base\Component;

/**
 * Component for maintenance mode behavior, focused on retrieve status from external configuration
 * Support ability for allow exclusive access in maintenance mode for specified ips
 * Support preliminar notice about soon maintenance works
 * Provide observable events when  maintenance process or preliminar modes
 * Class Maintenance
 *
 * @package insolita\techmode
 */
class Maintenance extends Component implements BootstrapInterface
{
    /**
     *
     */
    const EVENT_MAINTENANCE_PROCESS = 'maintenance_process';
    const EVENT_MAINTENANCE_SOON = 'maintenance_soon';
    
    /**
     * @var bool
     */
    public $globalEnabled = false;
    
    /**
     * @var bool
     */
    public $preliminarEnabled = false;
    
    /**
     * Flag for users, that have access in maintenance process
     *
     * @var bool
     */
    public $isSkipForIp = false;
    
    /**
     * Key for techmode status info
     *
     * @var string
     */
    public $enabledKey = 'techmode.enabled';
    
    /**
     * Key for preliminar status info about soon maintenance (if this behavior needed)
     *
     * @var string
     */
    public $preliminarKey = 'techmode.preliminar';
    
    /**
     * Key for comma-separated ip list, who can got app access even on maintenance process
     *
     * @var string
     */
    public $ipSkippedKey = 'techmode.ipSkip';
    
    /**
     * Route that catch all requests
     *
     * @var array|string
     */
    public $catchRoute = ['/site/techmode'];
    
    /**
     * Bootstrap method to be called during application bootstrap stage.
     *
     * @param \yii\web\Application $app the application currently running
     */
    public function bootstrap($app)
    {
        /**@var \insolita\maintenance\IConfig $config * */
        $config = \Yii::$container->get(IConfig::class);
        if ($this->globalEnabled === false) {
            $this->globalEnabled = boolval($config->get($this->enabledKey, false));
        }
        if ($this->globalEnabled === true) {
            $ips = $config->get($this->ipSkippedKey, '');
            $ips = \yii\helpers\StringHelper::explode($ips, ',', true, true);
            if ($this->checkUserIp($app->getRequest()->getUserIP(), $ips) === true) {
                $this->isSkipForIp = true;
            }
            $this->onMaintenanceProcessEvent();
            if ($this->isSkipForIp === false) {
                $app->catchAll = $this->catchRoute;
            }
        } elseif ($this->preliminarEnabled === true) {
            $this->onMaintenanceSoonNoticeEvent();
        } elseif ($this->preliminarKey) {
            $this->preliminarEnabled = boolval($config->get($this->preliminarKey, false));
            if ($this->preliminarEnabled === true) {
                $this->onMaintenanceSoonNoticeEvent();
            }
        }
    }
    
    /**
     * Attention! - event will be triggered on every request during maintenance process mode enabled
     */
    protected function onMaintenanceProcessEvent()
    {
        $this->trigger(self::EVENT_MAINTENANCE_PROCESS);
    }
    
    /**
     * Only if isset preliminar key
     * Attention! -event will be triggered on every request during  preliminar notice of maintenance  enabled!
     */
    protected function onMaintenanceSoonNoticeEvent()
    {
        $this->trigger(self::EVENT_MAINTENANCE_SOON);
    }
    
    /**
     * @param       $ip
     * @param array $allowedIps
     *
     * @return bool
     */
    protected function checkUserIp($ip, array $allowedIps = [])
    {
        if (!empty($allowedIps)) {
            foreach ($allowedIps as $filter) {
                if ($filter === '*'
                    || $filter === $ip
                    || (($pos = strpos($filter, '*')) !== false && !strncmp($ip, $filter, $pos))
                ) {
                    return true;
                    
                }
            }
        } else {
            return false;
        }
    }
}
