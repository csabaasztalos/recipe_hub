<?php
class CategoryManagerController implements IPageBase
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

            $mainTemplate = Template::Load("admin/manage-categories.html");

            $mainTemplate->AddData('ID', $this->model->LoadContent($pageData['pageID'], "ID")["content"]);
            $mainTemplate->AddData('TABLETITLE', $this->model->LoadContent($pageData['pageID'], "TABLETITLE")["content"]);
            $mainTemplate->AddData('USERID', $this->model->LoadContent($pageData['pageID'], "USERID")["content"]);
            $mainTemplate->AddData('UPLOADEDAT', $this->model->LoadContent($pageData['pageID'], "UPLOADEDAT")["content"]);
            $mainTemplate->AddData('STATUS', $this->model->LoadContent($pageData['pageID'], "STATUS")["content"]);
            $mainTemplate->AddData('ACTIONS', $this->model->LoadContent($pageData['pageID'], "ACTIONS")["content"]);
            $mainTemplate->AddData('TITLE', $this->model->LoadContent($pageData['pageID'], "TITLE")["content"]);

            $mainTemplate->AddData('FILTERSTATUS', $this->model->LoadContent($pageData['pageID'], 'FILTERSTATUS')["content"]);
            $mainTemplate->AddData('ALLOPTIONS', $this->model->LoadContent($pageData['pageID'], 'ALLOPTIONS')["content"]);
            $mainTemplate->AddData('FILTERFROMDATE', $this->model->LoadContent($pageData['pageID'], 'FILTERFROMDATE')["content"]);
            $mainTemplate->AddData('FILTERTODATE', $this->model->LoadContent($pageData['pageID'], 'FILTERTODATE')["content"]);
            $mainTemplate->AddData('FILTERSEARCH', $this->model->LoadContent($pageData['pageID'], 'FILTERSEARCH')["content"]);
            $mainTemplate->AddData('FILTERBUTTON', $this->model->LoadContent($pageData['pageID'], 'FILTERBUTTON')["content"]);
            $mainTemplate->AddData('RESETFILTER', $this->model->LoadContent($pageData['pageID'], 'RESETFILTER')["content"]);
            $mainTemplate->AddData('NEW', $this->model->LoadContent($pageData['pageID'], 'NEW')["content"]);
            $mainTemplate->AddData('ASSIGN', $this->model->LoadContent($pageData['pageID'], 'ASSIGN')["content"]);
            $mainTemplate->AddData('API', $this->model->LoadContent($pageData['pageID'], 'API')["content"]);
            $mainTemplate->AddData('REMOVE', $this->model->LoadContent($pageData['pageID'], 'REMOVE')["content"]);
            $mainTemplate->AddData('DELETESELECTED', $this->model->LoadContent($pageData['pageID'], 'DELETESELECTED')["content"]);

            $addCategoryTemplate = Template::Load("admin/addCategory.html");
            $addCategoryTemplate->AddData('NEWCATEGORY', $this->model->LoadContent($pageData['pageID'], 'NEWCATEGORY')["content"]);
            $addCategoryTemplate->AddData('CATEGORYNAME', $this->model->LoadContent($pageData['pageID'], 'CATEGORYNAME')["content"]);
            $addCategoryTemplate->AddData('ADDNEWCATEGORY', $this->model->LoadContent($pageData['pageID'], 'ADDNEWCATEGORY')["content"]);
            $addCategoryTemplate->AddData('USERID', $this->model->LoadContent($pageData['pageID'], 'USERID')["content"]);
            $addCategoryTemplate->AddData('STATUS', $this->model->LoadContent($pageData['pageID'], 'STATUS')["content"]);
            $addCategoryTemplate->AddData('CANCEL', $this->model->LoadContent($pageData['pageID'], 'CANCEL')["content"]);

            $assignCategoryTemplate = Template::Load("admin/assignCategory.html");
            $assignCategoryTemplate->AddData('ADDRECIPECATEGORY', $this->model->LoadContent($pageData['pageID'], 'ADDRECIPECATEGORY')["content"]);
            $assignCategoryTemplate->AddData('RECIPESSEARCH', $this->model->LoadContent($pageData['pageID'], 'RECIPESSEARCH')["content"]);
            $assignCategoryTemplate->AddData('CATEGORY', $this->model->LoadContent($pageData['pageID'], 'CATEGORY')["content"]);
            $assignCategoryTemplate->AddData('ADDNEWRECIPECATEGORY', $this->model->LoadContent($pageData['pageID'], 'ADDNEWRECIPECATEGORY')["content"]);
            $assignCategoryTemplate->AddData('CANCEL', $this->model->LoadContent($pageData['pageID'], 'CANCEL')["content"]);

            $removeCategoryTemplate = Template::Load("admin/removeCategory.html");
            $removeCategoryTemplate->AddData('REMOVERECIPECATEGORY', $this->model->LoadContent($pageData['pageID'], 'REMOVERECIPECATEGORY')["content"]);
            $removeCategoryTemplate->AddData('RECIPESSEARCH', $this->model->LoadContent($pageData['pageID'], 'RECIPESSEARCH')["content"]);
            $removeCategoryTemplate->AddData('ASSIGNEDCATEGORIES', $this->model->LoadContent($pageData['pageID'], 'ASSIGNEDCATEGORIES')["content"]);
            $removeCategoryTemplate->AddData('REMOVE', $this->model->LoadContent($pageData['pageID'], 'REMOVE')["content"]);
            $removeCategoryTemplate->AddData('CANCEL', $this->model->LoadContent($pageData['pageID'], 'CANCEL')["content"]);

            $importAPITemplate = Template::Load("admin/importAPI.html");
            $importAPITemplate->AddData('PREVIEWSTAGING', $this->model->LoadContent($pageData['pageID'], 'PREVIEWSTAGING')["content"]);
            $importAPITemplate->AddData('ID', $this->model->LoadContent($pageData['pageID'], 'ID')["content"]);
            $importAPITemplate->AddData('CATEGORYNAME', $this->model->LoadContent($pageData['pageID'], 'CATEGORYNAME')["content"]);
            $importAPITemplate->AddData('STATUS', $this->model->LoadContent($pageData['pageID'], 'STATUS')["content"]);
            $importAPITemplate->AddData('ACTIONS', $this->model->LoadContent($pageData['pageID'], 'ACTIONS')["content"]);
            $importAPITemplate->AddData('CLOSE', $this->model->LoadContent($pageData['pageID'], 'CLOSE')["content"]);
            $importAPITemplate->AddData('IMPORT', $this->model->LoadContent($pageData['pageID'], 'IMPORT')["content"]);
            $importAPITemplate->AddData('DELETESTAGING', $this->model->LoadContent($pageData['pageID'], 'DELETESTAGING')["content"]);
            $importAPITemplate->AddData('SAVETODB', $this->model->LoadContent($pageData['pageID'], 'SAVETODB')["content"]);

            $editCategoryTemplate = Template::Load("admin/editTemplate.html");
            $editCategoryTemplate->AddData('EDITCATEGORY', $this->model->LoadContent($pageData['pageID'], 'EDITCATEGORY')["content"]);
            $editCategoryTemplate->AddData('CATEGORYNAME', $this->model->LoadContent($pageData['pageID'], 'CATEGORYNAME')["content"]);
            $editCategoryTemplate->AddData('STATUS', $this->model->LoadContent($pageData['pageID'], 'STATUS')["content"]);
            $editCategoryTemplate->AddData('EDITCATEGORYBTN', $this->model->LoadContent($pageData['pageID'], 'EDITCATEGORYBTN')["content"]);
            $editCategoryTemplate->AddData('CANCEL', $this->model->LoadContent($pageData['pageID'], 'CANCEL')["content"]);

            $errorModal = Template::Load("admin/errorModal.html");
            $errorModal->AddData("ERROR", $this->model->LoadContent('admin', 'ERROR')['content']);
            $errorModal->AddData("OK", $this->model->LoadContent('admin', 'OK')['content']);

            global $cfg;
            [$dateFrom, $dateTo, $keyword, $status, $statusHtml] = AdminFilterService::GetFilterData($_GET, false, false, null);

            $filteredCategories = CategoryQueryService::GetFilteredCategories($keyword, $status, $dateFrom, $dateTo);
            [$desktopHtml, $mobileHtml] = CategoryManagerView::DrawMainCategoryTable($filteredCategories);
            $recipeList = RecipeService::GetAllRecipes();
            $recipesHtml = CategoryManagerView::BuildRecipeList($recipeList);
            $allCats = CategoryService::FetchCategories();
            $catListHtml = CategoryManagerView::BuildCategoryList($allCats, null);
            $categoryStatus = AdminView::BuildStatusOptions("");

            if (isset($_POST['category_id'])) {
                if (isset($_POST['approve'])) {
                    CategoryService::ApproveCategory((int) $_POST['category_id']);
                }
                if (isset($_POST['reject'])) {
                    CategoryService::RejectCategory((int) $_POST['category_id']);
                }
                if (isset($_POST['delete'])) {
                    CategoryService::DeleteCategory((int) $_POST['category_id']);
                }
                if (isset($_POST['addCategoryRecipe']) && isset($_POST['recipe_id'])) {
                    CategoryManagerService::AssignRecipeCategory($_POST['category_id'], (int) $_POST['recipe_id']);
                }
                if (isset($_POST['removeCategoryRecipe']) && isset($_POST['recipe_id'])) {
                    CategoryQueryService::RemoveRecipeCategory((int) $_POST['category_id'], (int) $_POST['recipe_id']);
                }
                if (isset($_POST['editCategory']) && isset($_POST['status']) && isset($_POST['categoryName'])) {
                    CategoryService::EditCategory((int) $_POST['category_id'], $_POST['categoryName'], $_POST['status']);
                }
            }

            if (isset($_POST['newCategory']) && isset($_POST['status']) &&
                isset($_POST['userid']) && isset($_POST['categoryName'])) {
                CategoryService::AddNewCategory($_POST['categoryName'], $_POST['status'], (int) $_POST['userid'], "manual", null);
            }

            if (isset($_POST['bulkDelete']) && isset($_POST['recordIDS']) ) {
                CategoryService::DeleteCategory($_POST['recordIDS']);
            }

            $stagedCategories = CategoryQueryService::GetCategoriesBySource("api", "staging");
            $importTable = CategoryManagerView::DrawImportTable($stagedCategories);
            $importAPITemplate->AddData("FETCHEDDATA", $importTable);

            if (isset($_POST['importAPIBtn'])) {
                CategoryManagerService::ImportCategories();
            }
            if (isset($_POST['deleteStagingBtn']) && isset($_POST['recordIDS'])) {
                CategoryManagerService::DeleteImportedCategories($_POST['recordIDS']);
            }
            if (isset($_POST['saveStagingBtn']) && isset($_POST['recordIDS'])) {
                CategoryManagerService::SaveImportedCategories($_POST['recordIDS']);
            }

            if (isset($_POST['logout'])) {
                Logout::DestroySession();
            }
        } catch (DBException $ex) {
            throw new PageLoadException("Adatbázis hiba miatt az oldal betöltése sikertelen. ");
        } catch (CategoryServiceException | DBException $ex) {
            throw new PageLoadException("Adatbázis hiba miatt az oldal betöltése sikertelen. ");
        } catch (CategoryActionException $ex) {
            $errorModal->AddData("ERRORMESSAGE", $ex->getMessage());
        } catch (AssignCategoryException $ex) {
            $keyword = $_POST['recipeSearch'] ?? "";
            $recipeId = $_POST['recipe_id'] ?? "";
            $categoryId = $_POST['category_id'] ?? null;
            $catListHtml = CategoryManagerView::BuildCategoryList($allCats, $categoryId);

            $assignCategoryTemplate->AddData("RECIPENAME", $keyword);
            $assignCategoryTemplate->AddData("RECIPEID", $recipeId);
            $assignCategoryTemplate->AddData("RECIPECATEGORYRESULT", $ex->getMessage());
        } catch (RemoveCategoryException $ex) {
            $keyword = $_POST['removeRecipeSearch'] ?? "";
            $recipeId = $_POST['recipe_id'] ?? "";
            $categoryId = $_POST['category_id'] ?? null;
            $catListHtml = CategoryManagerView::BuildCategoryList($allCats, $categoryId);

            $removeCategoryTemplate->AddData("RECIPENAME", $keyword);
            $removeCategoryTemplate->AddData("RECIPEID", $recipeId);
            $removeCategoryTemplate->AddData("RECIPECATEGORYREMOVERESULT", $ex->getMessage());
        } catch (EditCategoryException $ex) {
            $editCategoryTemplate->AddData("EDITCATEGORYRESULT", $ex->getMessage());
        } catch (NewCategoryException $ex) {
            $categoryName = $_POST['categoryName'] ?? "";
            $userId = $_POST['userid'] ?? "";
            $status = $_POST['status'] ?? "";
            $categoryStatus = AdminView::BuildStatusOptions($status);

            $addCategoryTemplate->AddData("CATNAME", $categoryName);
            $addCategoryTemplate->AddData("USERIDVALUE", $userId);
            $addCategoryTemplate->AddData("NEWCATEGORYRESULT", $ex->getMessage());
        } catch (ImportException $ex) {
            $importAPITemplate->AddData("IMPORTRESULT", $ex->getMessage());
        } finally {
            $mainTemplate->AddData('CATEGORYLIST', $desktopHtml);
            $mainTemplate->AddData('MOBILECATEGORIES', $mobileHtml);
            $assignCategoryTemplate->AddData('CHOOSECATEGORY', $catListHtml);
            $removeCategoryTemplate->AddData('ASSIGNEDCATEGORYOPTIONS', $catListHtml);
            $assignCategoryTemplate->AddData('RECIPELIST', $recipesHtml);
            $removeCategoryTemplate->AddData('RECIPELIST', $recipesHtml);
            $mainTemplate->AddData("STATUSOPTIONS", $statusHtml);
            $addCategoryTemplate->AddData('STATUSOPTIONS', $categoryStatus);
            $editCategoryTemplate->AddData('STATUSOPTIONS', $categoryStatus);

            $mainTemplate->AddData("ASSIGNCATEGORYMODAL", $assignCategoryTemplate);
            $mainTemplate->AddData("ADDCATEGORYMODAL", $addCategoryTemplate);
            $mainTemplate->AddData("IMORTAPIMODAL", $importAPITemplate);
            $mainTemplate->AddData("REMOVECATEGORYMODAL", $removeCategoryTemplate);
            $mainTemplate->AddData("EDITCATEGORYMODAL", $editCategoryTemplate);
            $mainTemplate->AddData("ERRORMODAL", $errorModal);
            $this->template->AddData("MAIN", $mainTemplate);
        }

    }
}
