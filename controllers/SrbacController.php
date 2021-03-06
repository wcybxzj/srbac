<?php 

/**
 * @title 权限基类
 * @auth wsq cboy868@163.com
 */

namespace backend\modules\srbac\controllers;

use Yii;
use yii\web\Controller;
use yii\web\BadRequestHttpException;
use yii\helpers\Url;
class SrbacController extends Controller{

    public function init()
    {
        //判断是否登录
        if (Yii::$app->user->isGuest)
            $this->redirect(Url::toRoute('/site/login'));
    }

    public function ajaxReturn($data = null, $info = '', $success = true) {
        header('Content-type: application/json');
        $all = [
        	'status' => $success, 
        	'info' 	=> $info, 
        	'data'	=> $data,
        	'csrf'	=> Yii::$app->request->getCsrfToken()
        ];
        echo  json_encode($all);
        exit;
    }
    /**
     * @title 权限验证
     */
    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {
            $ac = $this->getFullAction($action);
            $auth = Yii::$app->authManager;

            //判断是否是普通用户，如果是，跳到前台
            $role = $auth->getRolesByUser(Yii::$app->user->getId());
            $customer_role = $auth->getRole('customer');
            if (isset($role['customer']) && $role['customer'] == $customer_role) {
                Yii::$app->getSession()->setFlash('error', '非法操作');
                $this->redirect('/');
            }
            
            if (($auth->getPermission($ac) && !\Yii::$app->user->can($ac)) && \Yii::$app->user->id!=1) {
                throw new BadRequestHttpException(Yii::t('yii', '您无权进行此操作'));
            }
            return true;
        } else {
            return false;
        }
    }
    /**
     * @title 取得权限全名
     */
    private function getFullAction($action)
    {
        $namespace = str_replace('\controllers', '', \Yii::$app->controllerNamespace);
        $mod = \Yii::$app->controller->module !== null ? '@'.\Yii::$app->controller->module->id : "";
        $controller = \Yii::$app->controller->id;
        $ac = $action->id;
        return $namespace.$mod.'-'.$controller.'-'.$ac;
    }
}