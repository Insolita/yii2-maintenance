<?php
/**
 * Created by solly [01.06.17 7:08]
 */

namespace tests\unit;

use Codeception\Test\Unit;
use Codeception\Util\Stub;
use insolita\maintenance\IConfig;
use insolita\maintenance\Maintenance;
use yii\web\Application;
use yii\web\Request;

/**
 * Class MaintenanceTest
 *
 * @package tests\unit
 */
class MaintenanceTest extends Unit
{
    
    /**
     *
     */
    public function testNotMaintenance()
    {
        $request = Stub::make(Request::class, ['getUserIp' => Stub::never()], $this);
        $app = Stub::makeEmpty(Application::class, ['catchAll' => Stub::never(), 'getRequest' => $request],$this);
        $config = Stub::make(DummyConfig::class,[
            'get'=>Stub::exactly(2, function(){
                 return false;
            })
        ],$this);
        \Yii::$container->set(IConfig::class, $config);
        /**@var Maintenance $component * */
        $component = Stub::make(
            Maintenance::class,
            [
                'onMaintenanceProcessEvent'    => Stub::never(),
                'onMaintenanceSoonNoticeEvent' => Stub::never(),
                'checkUserIp'                  => Stub::never(),
            ]
        );
        verify($component->globalEnabled)->false();
        verify($component->isSkipForIp)->false();
        verify($component->preliminarEnabled)->false();
        $component->bootstrap($app);
        verify($component->globalEnabled)->false();
        verify($component->preliminarEnabled)->false();
        verify($component->isSkipForIp)->false();
    }
    
    /**
     *
     */
    public function testMaintenanceGlobal()
    {
        $request = Stub::make(Request::class, ['getUserIp' => Stub::once(function(){ return '127.0.0.1';})], $this);
        $app = Stub::makeEmpty(Application::class, ['catchAll' => Stub::once(), 'getRequest' => $request],$this);
        $config = Stub::make(DummyConfig::class,[
            'get'=>Stub::once(function(){
                return '';
            })
        ],$this);
        \Yii::$container->set(IConfig::class, $config);
        /**@var Maintenance $component * */
        $component = Stub::make(
            Maintenance::class,
            [
                'globalEnabled'=>true,
                'onMaintenanceProcessEvent'    => Stub::once(function (){}),
                'onMaintenanceSoonNoticeEvent' => Stub::never(),
                'checkUserIp'                  => Stub::once(function(){ return false;}),
            ]
        );
        verify($component->globalEnabled)->true();
        verify($component->preliminarEnabled)->false();
        verify($component->isSkipForIp)->false();
        $component->bootstrap($app);
        verify($component->globalEnabled)->true();
        verify($component->preliminarEnabled)->false();
        verify($component->isSkipForIp)->false();
    }
    
    /**
     *
     */
    public function testMaintenanceGlobalSkip()
    {
        $request = Stub::make(Request::class, ['getUserIp' => Stub::once(function(){ return '127.0.0.1';})], $this);
        $app = Stub::makeEmpty(Application::class, ['catchAll' => Stub::once(), 'getRequest' => $request],$this);
        $config = Stub::make(DummyConfig::class,[
            'get'=>Stub::once(function(){
                return '';
            })
        ],$this);
        \Yii::$container->set(IConfig::class, $config);
        /**@var Maintenance $component * */
        $component = Stub::make(
            Maintenance::class,
            [
                'globalEnabled'=>true,
                'onMaintenanceProcessEvent'    => Stub::once(function (){}),
                'onMaintenanceSoonNoticeEvent' => Stub::never(),
                'checkUserIp'                  => Stub::once(function(){ return true;}),
            ]
        );
        verify($component->globalEnabled)->true();
        verify($component->preliminarEnabled)->false();
        verify($component->isSkipForIp)->false();
        $component->bootstrap($app);
        verify($component->globalEnabled)->true();
        verify($component->preliminarEnabled)->false();
        verify($component->isSkipForIp)->true();
    }
    
    /**
     *
     */
    public function testMaintenanceGlobalAndPreliminar()
    {
        $request = Stub::make(Request::class, ['getUserIp' => Stub::once(function(){ return '127.0.0.1';})], $this);
        $app = Stub::make(Application::class, ['getRequest' => $request,'catchAll'=>Stub::once()],$this);
        $config = Stub::make(DummyConfig::class,[
            'get'=>Stub::once(function(){
                return '';
            })
        ],$this);
        \Yii::$container->set(IConfig::class, $config);
        /**@var Maintenance $component * */
        $component = Stub::make(
            Maintenance::class,
            [
                'globalEnabled'=>true,
                'preliminarEnabled'=>true,
                'onMaintenanceProcessEvent'    => Stub::once(function (){}),
                'onMaintenanceSoonNoticeEvent' => Stub::never(),//Important!
                'checkUserIp'                  => Stub::once(function(){ return true;}),
            ]
        );
        verify($component->globalEnabled)->true();
        verify($component->preliminarEnabled)->true();
        verify($component->isSkipForIp)->false();
        $component->bootstrap($app);
        verify($component->globalEnabled)->true();
        verify($component->preliminarEnabled)->true();
        verify($component->isSkipForIp)->true();
    }
    
    /**
     *
     */
    public function testMaintenancePreliminar()
    {
        $request = Stub::make(Request::class, ['getUserIp' => Stub::never()], $this);
        $app = Stub::makeEmpty(Application::class, ['catchAll' => Stub::never(), 'getRequest' => $request],$this);
        $config = Stub::make(DummyConfig::class,[
            'get'=>Stub::once(function(){
                return '';
            })
        ],$this);
        \Yii::$container->set(IConfig::class, $config);
        /**@var Maintenance $component * */
        $component = Stub::make(
            Maintenance::class,
            [
                'globalEnabled'=>false,
                'preliminarEnabled'=>true,
                'onMaintenanceProcessEvent'    => Stub::never(),
                'onMaintenanceSoonNoticeEvent' => Stub::once(function (){}),
                'checkUserIp'                  => Stub::never()
            ]
        );
        verify($component->globalEnabled)->false();
        verify($component->preliminarEnabled)->true();
        verify($component->isSkipForIp)->false();
        $component->bootstrap($app);
        verify($component->globalEnabled)->false();
        verify($component->preliminarEnabled)->true();
        verify($component->isSkipForIp)->false();
    }
    
    /**
     *
     */
    public function testMaintenancePreliminarByConfig()
    {
        $request = Stub::make(Request::class, ['getUserIp' => Stub::never()], $this);
        $app = Stub::makeEmpty(Application::class, ['catchAll' => Stub::never(), 'getRequest' => $request],$this);

        $config = Stub::make(DummyConfig::class,[
            'get'=>Stub::exactly(2, function($key){
               if($key=='techmode.enabled'){
                   return false;
               }else{
                   return true;
               }
            })
        ],$this);
        \Yii::$container->set(IConfig::class, $config);
        /**@var Maintenance $component * */
        $component = Stub::make(
            Maintenance::class,
            [
                'globalEnabled'=>false,
                'preliminarEnabled'=>false,
                'onMaintenanceProcessEvent'    => Stub::never(),
                'onMaintenanceSoonNoticeEvent' => Stub::once(function (){}),
                'checkUserIp'                  => Stub::never()
            ]
        );
        verify($component->preliminarKey)->equals('techmode.preliminar');
        verify($component->globalEnabled)->false();
        verify($component->preliminarEnabled)->false();
        verify($component->isSkipForIp)->false();
        $component->bootstrap($app);
        verify($component->globalEnabled)->false();
        verify($component->preliminarEnabled)->true();
        verify($component->isSkipForIp)->false();
    }
    
    /**
     *
     */
    public function testMaintenanceGlobalByConfig()
    {
        $request = Stub::make(Request::class, ['getUserIp' => Stub::once()], $this);
        $app = Stub::makeEmpty(Application::class, ['catchAll' => Stub::once(), 'getRequest' => $request],$this);
        
        $config = Stub::make(DummyConfig::class,[
            'get'=>Stub::exactly(2, function($key){
                if($key=='techmode.enabled'){
                    return true;
                }else{
                    return '';
                }
            })
        ],$this);
        \Yii::$container->set(IConfig::class, $config);
        /**@var Maintenance $component * */
        $component = Stub::make(
            Maintenance::class,
            [
                'globalEnabled'=>false,
                'preliminarEnabled'=>false,
                'onMaintenanceProcessEvent'    => Stub::once(),
                'onMaintenanceSoonNoticeEvent' => Stub::never(),
                'checkUserIp'                  => Stub::once(function (){return false;})
            ]
        );
        verify($component->globalEnabled)->false();
        verify($component->preliminarEnabled)->false();
        verify($component->isSkipForIp)->false();
        $component->bootstrap($app);
        verify($component->globalEnabled)->true();
        verify($component->preliminarEnabled)->false();
        verify($component->isSkipForIp)->false();
    }
}
