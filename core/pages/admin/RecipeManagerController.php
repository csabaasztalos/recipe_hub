<?php
class RecipeManagerController implements IPageBase
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

            $mainTemplate = Template::Load("admin/manage-recipes.html");

            $mainTemplate->AddData('ID', $this->model->LoadContent($pageData['pageID'], "ID")["content"]);
            $mainTemplate->AddData('TABLETITLE', $this->model->LoadContent($pageData['pageID'], "TABLETITLE")["content"]);
            $mainTemplate->AddData('USERID', $this->model->LoadContent($pageData['pageID'], "USERID")["content"]);
            $mainTemplate->AddData('UPLOADEDAT', $this->model->LoadContent($pageData['pageID'], "UPLOADEDAT")["content"]);
            $mainTemplate->AddData('STATUS', $this->model->LoadContent($pageData['pageID'], "STATUS")["content"]);
            $mainTemplate->AddData('ACTIONS', $this->model->LoadContent($pageData['pageID'], "ACTIONS")["content"]);
            $mainTemplate->AddData('TITLE', $this->model->LoadContent($pageData['pageID'], "TITLE")["content"]);

            $mainTemplate->AddData('FILTERSTATUS', $this->model->LoadContent($pageData['pageID'], 'FILTERSTATUS')["content"]);
            $mainTemplate->AddData('ALLOPTIONS', $this->model->LoadContent($pageData['pageID'], 'ALLOPTIONS')["content"]);
            $mainTemplate->AddData('FILTERCATEGORY', $this->model->LoadContent($pageData['pageID'], 'FILTERCATEGORY')["content"]);
            $mainTemplate->AddData('FILTERFROMDATE', $this->model->LoadContent($pageData['pageID'], 'FILTERFROMDATE')["content"]);
            $mainTemplate->AddData('FILTERTODATE', $this->model->LoadContent($pageData['pageID'], 'FILTERTODATE')["content"]);
            $mainTemplate->AddData('FILTERSEARCH', $this->model->LoadContent($pageData['pageID'], 'FILTERSEARCH')["content"]);
            $mainTemplate->AddData('FILTERBUTTON', $this->model->LoadContent($pageData['pageID'], 'FILTERBUTTON')["content"]);
            $mainTemplate->AddData('RESETFILTER', $this->model->LoadContent($pageData['pageID'], 'RESETFILTER')["content"]);

            $errorModal = Template::Load("admin/errorModal.html");
            $errorModal->AddData("ERROR", $this->model->LoadContent('admin', 'ERROR')['content']);
            $errorModal->AddData("OK", $this->model->LoadContent('admin', 'OK')['content']);

            $cats = CategoryService::FetchCategories();
            [$dateFrom, $dateTo, $keyword, $status, $statusHtml, $category, $categoryHtml] = AdminFilterService::GetFilterData($_GET, false, true, $cats);

            $mainTemplate->AddData("DATEFROM", $dateFrom);
            $mainTemplate->AddData("DATETO", $dateTo);
            $mainTemplate->AddData("STATUSOPTIONS", $statusHtml);
            $mainTemplate->AddData("CATEGORYOPTIONS", $categoryHtml);
            $mainTemplate->AddData("SEARCH", $keyword);

            $filteredRecipes = AdminFilterService::ParseFilter($keyword, $category, $status, $dateFrom, $dateTo);
            [$desktopHtml,  $mobileHtml] = RecipeManagerView::DrawMainRecipeTable($filteredRecipes);
            $mainTemplate->AddData('RECIPELIST', $desktopHtml);
            $mainTemplate->AddData('MOBILERECIPELIST', $mobileHtml);

            if (isset($_POST['recipe_id'])) {
                $statusChanged = false;
                if (isset($_POST['approve'])) {
                    RecipeService::ApproveRecipe($_POST['recipe_id']);
                    $statusChanged = true;
                }
                if (isset($_POST['reject'])) {
                    RecipeService::RejectRecipe($_POST['recipe_id']);
                    $statusChanged = true;

                }
                if (isset($_POST['delete'])) {
                    RecipeService::DeleteRecipe($_POST['recipe_id']);
                    $statusChanged = true;
                }

                if (isset($_POST['userId']) && $statusChanged) {
                    $recipeData = RecipeService::FetchRecipeData($_POST['recipe_id']);
                    $recipeTitle = $recipeData['title'];
                    $recipeStatus = $recipeData['status'];
                    $newStatus = "Törölt";
                    match ($recipeStatus) {
                        "approved" => $newStatus = "Elfogadva",
                        "rejected" => $newStatus = "Elutasítva",
                        "pending" => $newStatus = "Függőben",
                    };

                    $userData = UserQueryService::GetUserById($recipeData['user_id']);
                    $emailTemp = file_get_contents($cfg['emailFolder']."recipeStatus.html");
                    $finalEmail = str_replace(["RECIPETITLE", "RECIPESTATUS"], [$recipeTitle, $newStatus], $emailTemp);
                    $altbody = "A Recepted státusza megváltozott!\nFeltöltött recepted ".($recipeTitle)." új státusza: ".$newStatus;

                    SendMail::Send([$userData['email']], "Recepet státuszváltozás", $finalEmail, $altbody);
                    header("Location: {$cfg['recipeManagerPage']}");
                    exit();
                }
            }

            if (isset($_POST['logout'])) {
                Logout::DestroySession();
            }

        } catch (DBException $ex) {
            throw new PageLoadException("Adatbázis hiba miatt az oldal betöltése sikertelen. ");
        } catch (RecipeActionException $ex) {
            $errorModal->AddData("ERRORMESSAGE", $ex->getMessage());
        } catch (MailException $ex) {
            $errorModal->AddData('ERRORMESSAGE', "Email kiküldése hibába ütközött! ".$ex->getMessage());
        } finally {
            $mainTemplate->AddData("ERRORMODAL", $errorModal);
            $this->template->AddData("MAIN", $mainTemplate);
        }
    }
}
