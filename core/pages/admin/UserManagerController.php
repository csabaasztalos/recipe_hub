<?php

use function Dom\import_simplexml;
class UserManagerController implements IPageBase
{

    private Template $template;

    private Model $model;

    public function __construct()
    {
        $this->model = Container::Get("model");
    }

    public function GetTemplate(): Template
    {
        return $this->template;
    }

    public function Run(array $pageData): void
    {
        try {
            global $cfg;
            $this->template = Template::Load("admin/".$pageData['template']);
            $userId = $_SESSION[$cfg['permissionSessionKey']]['data']['id'] ?? null;
            ValidateUser::Validate($userId);

            $this->template->AddData('TITLE', $this->model->LoadContent($pageData['pageID'], "TITLE")["content"]);
            $this->template->AddData('HOME', $this->model->LoadContent($pageData['pageID'], "HOME")["content"]);
            $this->template->AddData('RECIPES', $this->model->LoadContent($pageData['pageID'], "RECIPES")["content"]);
            $this->template->AddData('CATEGORIES', $this->model->LoadContent($pageData['pageID'], "CATEGORIES")["content"]);
            $this->template->AddData('INGREDIENTS', $this->model->LoadContent($pageData['pageID'], "INGREDIENTS")["content"]);
            $this->template->AddData('USERS', $this->model->LoadContent($pageData['pageID'], "USERS")["content"]);
            $this->template->AddData('LOGOUT', $this->model->LoadContent('admin', "LOGOUT")["content"]);

            $mainTemplate = Template::Load("admin/manage-users.html");

            $mainTemplate->AddData('TITLE', $this->model->LoadContent($pageData['pageID'], "TITLE")["content"]);
            $mainTemplate->AddData('NEW', $this->model->LoadContent($pageData['pageID'], "NEW")["content"]);
            $mainTemplate->AddData('ID', $this->model->LoadContent($pageData['pageID'], "ID")["content"]);
            $mainTemplate->AddData('USERNAME', $this->model->LoadContent($pageData['pageID'], "USERNAME")["content"]);
            $mainTemplate->AddData('EMAIL', $this->model->LoadContent($pageData['pageID'], "EMAIL")["content"]);
            $mainTemplate->AddData('CREATEDAT', $this->model->LoadContent($pageData['pageID'], "CREATEDAT")["content"]);
            $mainTemplate->AddData('PERMISSION', $this->model->LoadContent($pageData['pageID'], "PERMISSION")["content"]);
            $mainTemplate->AddData('STATUS', $this->model->LoadContent($pageData['pageID'], "STATUS")["content"]);
            $mainTemplate->AddData('ACTIONS', $this->model->LoadContent($pageData['pageID'], "ACTIONS")["content"]);

            $mainTemplate->AddData('FILTERFROMDATE', $this->model->LoadContent($pageData['pageID'], 'FILTERFROMDATE')["content"]);
            $mainTemplate->AddData('FILTERTODATE', $this->model->LoadContent($pageData['pageID'], 'FILTERTODATE')["content"]);
            $mainTemplate->AddData('FILTERSEARCH', $this->model->LoadContent($pageData['pageID'], 'FILTERSEARCH')["content"]);
            $mainTemplate->AddData('FILTERBUTTON', $this->model->LoadContent($pageData['pageID'], 'FILTERBUTTON')["content"]);
            $mainTemplate->AddData('RESETFILTER', $this->model->LoadContent($pageData['pageID'], 'RESETFILTER')["content"]);

            $addUserTemplate = Template::Load("admin/addUser.html");
            $addUserTemplate->AddData('NEWUSER', $this->model->LoadContent($pageData['pageID'], 'NEWUSER')["content"]);
            $addUserTemplate->AddData('EMAIL', $this->model->LoadContent($pageData['pageID'], 'EMAIL')["content"]);
            $addUserTemplate->AddData('PERMISSIONLVL', $this->model->LoadContent($pageData['pageID'], 'PERMISSIONLVL')["content"]);
            $addUserTemplate->AddData('ADDNEWUSER', $this->model->LoadContent($pageData['pageID'], 'ADDNEWUSER')["content"]);
            $addUserTemplate->AddData('USERNAME', $this->model->LoadContent($pageData['pageID'], 'USERNAME')["content"]);
            $addUserTemplate->AddData('CANCEL', $this->model->LoadContent($pageData['pageID'], 'CANCEL')["content"]);


            $editUserTemplate = Template::Load("admin/edit-user.html");
            $editUserTemplate->AddData('EDITUSER', $this->model->LoadContent($pageData['pageID'], "EDITUSER")["content"]);
            $editUserTemplate->AddData('USERNAME', $this->model->LoadContent($pageData['pageID'], "USERNAME")["content"]);
            $editUserTemplate->AddData('EMAIL', $this->model->LoadContent($pageData['pageID'], "EMAIL")["content"]);
            $editUserTemplate->AddData('PERMISSIONLVL', $this->model->LoadContent($pageData['pageID'], "PERMISSIONLVL")["content"]);
            $editUserTemplate->AddData('STATUS', $this->model->LoadContent($pageData['pageID'], "STATUS")["content"]);
            $editUserTemplate->AddData('NEWPASS', $this->model->LoadContent($pageData['pageID'], "NEWPASS")["content"]);
            $editUserTemplate->AddData('CANCEL', $this->model->LoadContent($pageData['pageID'], "CANCEL")["content"]);

            $errorModal = Template::Load("admin/errorModal.html");
            $errorModal->AddData("ERROR", $this->model->LoadContent('admin', 'ERROR')['content']);
            $errorModal->AddData("OK", $this->model->LoadContent('admin', 'OK')['content']);

            global $cfg;
            [$dateFrom, $dateTo, $keyword] = AdminFilterService::GetFilterData($_GET, true, false, null);
            $filteredUsers = UserQueryService::GetFilteredUsers($keyword, $dateFrom, $dateTo);
            [$desktopHtml, $mobileHtml] = UserManagerView::DrawMainUserTable($filteredUsers);
            $mainTemplate->AddData('USERSLIST', $desktopHtml);
            $mainTemplate->AddData('MOBILEUSERSLIST', $mobileHtml);

            $permissionHTML = UserManagerView::BuildPermissionOptions(null);
            $statusHTML = UserManagerView::BuildStatusOptions();
            $editUserTemplate->AddData('PERMISSIONOPTIONS', $permissionHTML);
            $editUserTemplate->AddData('STATUSOPTIONS', $statusHTML);

            if (isset($_POST['user_id']) && isset($_POST['delete'])) {
                UserService::DeleteUser((int) $_POST['user_id']);
            }

            if (isset($_POST['userEmail']) && isset($_POST['newUser']) && isset($_POST['userName']) && isset($_POST['permisson'])) {
                UserService::CreateUser($_POST['userName'], $_POST['userEmail'], $_POST['permisson']);
            }

            if (isset($_POST['updateUser']) && isset($_POST['userId']) && isset($_POST['permission']) && isset($_POST['userEmail']) && isset($_POST['userName'])) {
                UserService::EditUser((int) $_POST['userId'], $_POST['userName'], $_POST['userEmail'], (int) $_POST['permission'], null, (int) $_POST['status']);
            }

            if (isset($_POST['newPass']) && isset($_POST['userId']) && isset($_POST['permission']) && isset($_POST['userEmail']) && isset($_POST['userName'])) {
                $newPass = GeneratePassword::Generate(12);
                UserService::EditUser((int) $_POST['userId'], $_POST['userName'], $_POST['userEmail'], (int) $_POST['permission'], $newPass, (int) $_POST['status']);
            }

            if (isset($_POST['logout'])) {
                Logout::DestroySession();
            }
        } catch (DBException $ex) {
            throw new PageLoadException("Adatbázis hiba miatt az oldal betöltése sikertelen. ");
        } catch (DeleteUserException $ex) {
            $errorModal->AddData("ERRORMESSAGE", $ex->getMessage());
        } catch (CreateUserException $ex) {
            $addUserTemplate->AddData("USERNAMEVALUE", $_POST['userName'] ?? '');
            $addUserTemplate->AddData("USEREMAILVALUE", $_POST['userEmail'] ?? '');
            $permission = $_POST['permisson'] ?? null;
            $addUserTemplate->AddData("PERMISSIONVALUE", $_POST['userEmail'] ?? '');
            $permissionHTML = UserManagerView::BuildPermissionOptions($permission);
            
            $addUserTemplate->AddData("USERRESULT", $ex->getMessage());
        } catch (EditUserException $ex) {
            $editUserTemplate->AddData("ERRORID", $_POST['userId'] ?? '');
            $editUserTemplate->AddData("ERRORUSERNAME", $_POST['userName'] ?? '');
            $editUserTemplate->AddData("ERROREMAIL", $_POST['userEmail'] ?? '');
            $editUserTemplate->AddData("ERRORPERMISSION", $_POST['permission'] ?? '');
            $editUserTemplate->AddData("EDITRESULTS", $ex->getMessage());
        } finally {
            $mainTemplate->AddData("ADDUSERMODAL", $addUserTemplate);
            $mainTemplate->AddData("EDITMODAL", $editUserTemplate);
            $mainTemplate->AddData("ERRORMODAL", $errorModal);
            $addUserTemplate->AddData('PERMISSIONOPTIONS', $permissionHTML);
            $this->template->AddData("MAIN", $mainTemplate);
        }
    }
}
