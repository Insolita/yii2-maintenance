<?php
/**
 * Created by solly [01.06.17 6:17]
 */

namespace insolita\maintenance;

use yii\base\Action;

/**
 * Class MaintenanceAction
 * Simple Action example
 * @package insolita\maintenance
 */
class MaintenanceAction extends Action
{
    /**
     * @var string|callable
     */
    public $message;
    
    /**
     * @var int|callable
     */
    public $retryAfterTime = 300;
    
    /**
     * @var string
     */
    public $viewName;
    
    /**
     * @var string|bool
     */
    public $layout = false;
    
    /**
     * @return string
     */
    public function run()
    {
        if (is_callable($this->message)) {
            $this->message = call_user_func($this->message);
        }
        if (is_callable($this->retryAfterTime)) {
            $this->retryAfterTime = call_user_func($this->retryAfterTime);
        }
        \Yii::$app->response->statusCode = 503;
        if (!empty($this->retryAfterTime)) {
            \Yii::$app->response->headers->set('Retry-After', $this->retryAfterTime);
        }
        $this->controller->layout = $this->layout;
        return $this->controller->render(
            $this->viewName,
            [
                'message'        => $this->message,
                'retryAfterTime' => $this->retryAfterTime,
            ]
        );
    }
}
