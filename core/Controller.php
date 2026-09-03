<?php

final class Controller
{
    /**
     * @throws TemplateException
     * @throws PermissionException
     * @throws PageLoadException
     */
    public static function Route()
    {
        global $cfg;
        $page = $cfg['mainPage'];

        if (isset($_GET[$cfg['pageID']])) {
            $page = htmlspecialchars($_GET[$cfg['pageID']]);
        }
        try {
            $model = new Model();
            View::setBaseTemplate(Template::Load("main/".$cfg['mainTemplatePage']));
            $pageData = $model->GetPageData($page);

            if ($pageData["enabled"] !== 1) {
                throw new PermissionException('This page is disabled.');
            }
            if (! PermissionHandler::CheckPermission($pageData["permission_level"])) {
                throw new PermissionException('You do not have permission to access this page.');
            }

            if (class_exists($pageData['class']) && in_array('IPageBase', class_implements($pageData['class']))) {
                $pageObj = new $pageData['class']();
                $pageObj->Run($pageData);
                $results = $pageObj->GetTemplate();

                if ($results !== null) {
                    if ($pageData['full_template'] === 1) {
                        View::setBaseTemplate($results);
                    } else {
                        $parentData = $model->GetPageData($pageData['parent']);
                        View::setBaseTemplate(Template::Load($parentData['template']));
                        View::getBaseTemplate()->AddData($cfg['flagTemp'], $results);
                    }
                } else {
                    throw new PageLoadException('This page is missing data');
                }
            } else {
                throw new PageLoadException('This page does not exist or does not implement the correct interface.');
            }
        } catch (NotFoundException $e) {
            View::setBaseTemplate(Template::Load($cfg['notFoundPage']));
            View::getBaseTemplate()->AddData($cfg["defaultContentFlag"], "/content/images/page-not-found.png");
        } catch (PermissionException $e) {
            if ($pageData["permission_level"] === 1) {
                header("Location: {$cfg['loginPage']}");
                exit();
            } else {
                View::setBaseTemplate(Template::Load($cfg['permissionDenied']));
                View::getBaseTemplate()->AddData($cfg["defaultContentFlag"], "/content/images/access-denied.png");
            }

        } catch (Exception $e) {
            if ($cfg['maintenance'] && (is_a($e, 'exceptions\TemplateException')) ||
                is_a($e, 'exceptions\PageLoadException')) {
                View::setBaseTemplate(Template::Load($cfg['errorPage']));
                View::getBaseTemplate()->AddData('EXCEPTION', get_class($e));
                View::getBaseTemplate()->AddData('MESSAGE', $e->getMessage());
                View::getBaseTemplate()->AddData('TRACE', $e->getTraceAsString());
            } elseif (! $cfg['maintenance']) {
                View::setBaseTemplate(Template::Load($cfg['maintenancePage']));
            }
        } finally {
            try {
                Container::Get('db')->Disconnect();
            } catch (Exception) {
            }
            View::PrintFinalTemplate();
        }
    }
}
//TODO: Better Exception handleing and logging