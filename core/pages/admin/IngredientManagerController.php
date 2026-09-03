<?php
class IngredientManagerController implements IPageBase
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

            $mainTemplate = Template::Load("admin/manage-ingredients.html");

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
            $mainTemplate->AddData('REMOVE', $this->model->LoadContent($pageData['pageID'], 'REMOVE')["content"]);
            $mainTemplate->AddData('API', $this->model->LoadContent($pageData['pageID'], 'API')["content"]);
            $mainTemplate->AddData('DELETESELECTED', $this->model->LoadContent($pageData['pageID'], 'DELETESELECTED')["content"]);

            $addIngredientTemplate = Template::Load("admin/addCategory.html");
            $addIngredientTemplate->AddData('NEWCATEGORY', $this->model->LoadContent($pageData['pageID'], 'NEWINGREDIENT')["content"]);
            $addIngredientTemplate->AddData('CATEGORYNAME', $this->model->LoadContent($pageData['pageID'], 'INGREDIENTNAME')["content"]);
            $addIngredientTemplate->AddData('ADDNEWCATEGORY', $this->model->LoadContent($pageData['pageID'], 'ADDNEWINGREDIENT')["content"]);
            $addIngredientTemplate->AddData('USERID', $this->model->LoadContent($pageData['pageID'], "USERID")["content"]);
            $addIngredientTemplate->AddData('CANCEL', $this->model->LoadContent($pageData['pageID'], 'CANCEL')["content"]);

            $assignIngredientTemplate = Template::Load("admin/assignIngredient.html");
            $assignIngredientTemplate->AddData('ADDRECIPECATEGORY', $this->model->LoadContent($pageData['pageID'], 'ADDRECIPEINGREDIENT')["content"]);
            $assignIngredientTemplate->AddData('QTYANDUNIT', $this->model->LoadContent($pageData['pageID'], 'QTYANDUNIT')["content"]);
            $assignIngredientTemplate->AddData('RECIPESSEARCH', $this->model->LoadContent($pageData['pageID'], 'RECIPESSEARCH')["content"]);
            $assignIngredientTemplate->AddData('CATEGORY', $this->model->LoadContent($pageData['pageID'], 'INGREDIENT')["content"]);
            $assignIngredientTemplate->AddData('ADDNEWRECIPECATEGORY', $this->model->LoadContent($pageData['pageID'], 'ADDNEWRECIPEINGREDIENT')["content"]);
            $assignIngredientTemplate->AddData('CANCEL', $this->model->LoadContent($pageData['pageID'], 'CANCEL')["content"]);

            $removeIngredientTemplate = Template::Load("admin/removeCategory.html");
            $removeIngredientTemplate->AddData('REMOVERECIPECATEGORY', $this->model->LoadContent($pageData['pageID'], 'REMOVERECIPEINGREDIENT')["content"]);
            $removeIngredientTemplate->AddData('RECIPESSEARCH', $this->model->LoadContent($pageData['pageID'], 'RECIPESSEARCH')["content"]);
            $removeIngredientTemplate->AddData('ASSIGNEDCATEGORIES', $this->model->LoadContent($pageData['pageID'], 'ASSIGNEDINGREDIENTS')["content"]);
            $removeIngredientTemplate->AddData('REMOVE', $this->model->LoadContent($pageData['pageID'], 'REMOVE')["content"]);
            $removeIngredientTemplate->AddData('CANCEL', $this->model->LoadContent($pageData['pageID'], 'CANCEL')["content"]);

            $importAPITemplate = Template::Load("admin/importAPI.html");
            $importAPITemplate->AddData('PREVIEWSTAGING', $this->model->LoadContent($pageData['pageID'], 'PREVIEWSTAGING')["content"]);
            $importAPITemplate->AddData('ID', $this->model->LoadContent($pageData['pageID'], 'ID')["content"]);
            $importAPITemplate->AddData('CATEGORYNAME', $this->model->LoadContent($pageData['pageID'], 'INGREDIENTNAME')["content"]);
            $importAPITemplate->AddData('STATUS', $this->model->LoadContent($pageData['pageID'], 'STATUS')["content"]);
            $importAPITemplate->AddData('ACTIONS', $this->model->LoadContent($pageData['pageID'], 'ACTIONS')["content"]);
            $importAPITemplate->AddData('CLOSE', $this->model->LoadContent($pageData['pageID'], 'CLOSE')["content"]);
            $importAPITemplate->AddData('IMPORT', $this->model->LoadContent($pageData['pageID'], 'IMPORT')["content"]);
            $importAPITemplate->AddData('DELETESTAGING', $this->model->LoadContent($pageData['pageID'], 'DELETESTAGING')["content"]);
            $importAPITemplate->AddData('SAVETODB', $this->model->LoadContent($pageData['pageID'], 'SAVETODB')["content"]);

            $editIngredientTemplate = Template::Load("admin/editTemplate.html");
            $editIngredientTemplate->AddData('EDITCATEGORY', $this->model->LoadContent($pageData['pageID'], 'EDITINGREDIENT')["content"]);
            $editIngredientTemplate->AddData('CATEGORYNAME', $this->model->LoadContent($pageData['pageID'], 'INGREDIENTNAME')["content"]);
            $editIngredientTemplate->AddData('STATUS', $this->model->LoadContent($pageData['pageID'], 'STATUS')["content"]);
            $editIngredientTemplate->AddData('EDITCATEGORYBTN', $this->model->LoadContent($pageData['pageID'], 'EDITINGREDIENTBTN')["content"]);
            $editIngredientTemplate->AddData('CANCEL', $this->model->LoadContent($pageData['pageID'], 'CANCEL')["content"]);

            $errorModal = Template::Load("admin/errorModal.html");
            $errorModal->AddData("ERROR", $this->model->LoadContent('admin', 'ERROR')['content']);
            $errorModal->AddData("OK", $this->model->LoadContent('admin', 'OK')['content']);

            global $cfg;
            [$dateFrom, $dateTo, $keyword, $status, $statusHtml] = AdminFilterService::GetFilterData($_GET, false, false, null);

            $filteredIng = IngredientQueryService::GetFilteredIngredients($keyword, $status, $dateFrom, $dateTo);
            [$desktopHtml, $mobileHtml] = IngredientManagerView::DrawMainIngredientTable($filteredIng);
            $recipeList = RecipeService::GetAllRecipes();
            $recipesHtml = IngredientManagerView::BuildRecipeList($recipeList);
            $allIng = IngredientService::FetchIngredients();
            $ingredientListHtml = IngredientManagerView::BuildIngredientList($allIng, null);
            $ingStatus = AdminView::BuildStatusOptions("");
            

            if (isset($_POST['ingredient_id'])) {
                if (isset($_POST['approve'])) {
                    IngredientService::ApproveIngredient($_POST['ingredient_id']);
                }
                if (isset($_POST['reject'])) {
                    IngredientService::RejectIngredient($_POST['ingredient_id']);
                }
                if (isset($_POST['delete'])) {
                    IngredientService::DeleteIngredient($_POST['ingredient_id']);
                }
            }

            if (isset($_POST['category_id'])) {
                if (isset($_POST['addCategoryRecipe']) && isset($_POST['recipe_id'])) {
                    IngredientManagerService::AssignRecipeIngredient((int)$_POST['category_id'], $_POST['ingredients'], (int)$_POST['recipe_id']);
                }
                if (isset($_POST['removeCategoryRecipe']) && isset($_POST['recipe_id'])) {
                    IngredientQueryService::RemoveRecipeIngredient((int)$_POST['category_id'], (int)$_POST['recipe_id']);
                }
                if (isset($_POST['editCategory']) && isset($_POST['status']) && isset($_POST['categoryName'])) {
                    IngredientService::EditIngredient((int)$_POST['category_id'], $_POST['categoryName'], $_POST['status']);
                }
            }

            if (isset($_POST['newCategory']) && isset($_POST['status']) &&
                isset($_POST['userid']) && isset($_POST['categoryName'])) {
                IngredientService::AddNewIngredient($_POST['categoryName'], $_POST['status'], (int) $_POST['userid'], "manual", null);
            }

            if (isset($_POST['bulkDelete']) && isset($_POST['recordIDS']) ) {
                IngredientService::DeleteIngredient($_POST['recordIDS']);
            }

            $stagedIngredients = IngredientQueryService::GetIngredientsBySource("api", "staging");
            $importTable = IngredientManagerView::DrawImportTable($stagedIngredients);
            $importAPITemplate->AddData("FETCHEDDATA", $importTable);

            if (isset($_POST['importAPIBtn'])) {
                IngredientManagerService::ImportIngredients();
            }
            if (isset($_POST['deleteStagingBtn']) && isset($_POST['recordIDS'])) {
                IngredientManagerService::DeleteImportedIngredients($_POST['recordIDS']);
            }
            if (isset($_POST['saveStagingBtn']) && isset($_POST['recordIDS'])) {
                IngredientManagerService::SaveImportedIngredients($_POST['recordIDS']);
            }

            if (isset($_POST['logout'])) {
                Logout::DestroySession();
            }
            //csvImport
        } catch (DBException $ex) {
            throw new PageLoadException("Adatbázis hiba miatt az oldal betöltése sikertelen. ");
        } catch (IngredientServiceException |  DBException $ex) {
            throw new PageLoadException("Adatbázis hiba miatt az oldal betöltése sikertelen. ");
        } catch (IngredientActionException $ex) {
            $errorModal->AddData("ERRORMESSAGE", $ex->getMessage());
        } catch (AssignIngredientException $ex) {
            $keyword = $_POST['recipeSearch'] ?? "";
            $recipeId = $_POST['recipe_id'] ?? "";
            $recipeId = $_POST['recipe_id'] ?? "";
            $ingId = $_POST['category_id'] ?? null;
            $ingredientListHtml = IngredientManagerView::BuildIngredientList($allIng, $ingId);
            $qty = $_POST['ingredients']['qty'] ?? "";
            $unit = $_POST['ingredients']['unit'] ?? "";

            $assignIngredientTemplate->AddData("RECIPENAME", $keyword);
            $assignIngredientTemplate->AddData("QTYVALUE", $qty);
            $assignIngredientTemplate->AddData("UNITVALUE", $unit);
            $assignIngredientTemplate->AddData("RECIPEID", $recipeId);
            $assignIngredientTemplate->AddData("RECIPECATEGORYRESULT", $ex->getMessage());
        } catch (RemoveIngredientException $ex) {
            $keyword = $_POST['removeRecipeSearch'] ?? "";
            $recipeId = $_POST['recipe_id'] ?? "";
            $ingId = $_POST['category_id'] ?? null;
            $ingredientListHtml = IngredientManagerView::BuildIngredientList($allIng, $ingId);

            $removeIngredientTemplate->AddData("RECIPENAME", $keyword);
            $removeIngredientTemplate->AddData("RECIPEID", $recipeId);
            $removeIngredientTemplate->AddData("RECIPECATEGORYREMOVERESULT", $ex->getMessage());
        } catch (EditIngredientException $ex) {
            $editIngredientTemplate->AddData("EDITCATEGORYRESULT", $ex->getMessage());
        } catch (NewIngredientException $ex) {
            $ingName = $_POST['categoryName'] ?? "";
            $userId = $_POST['userid'] ?? "";
            $status = $_POST['status'] ?? "";
            $ingStatus = AdminView::BuildStatusOptions($status);

            $addIngredientTemplate->AddData("CATNAME", $ingName);
            $addIngredientTemplate->AddData("USERIDVALUE", $userId);
            $addIngredientTemplate->AddData("NEWCATEGORYRESULT", $ex->getMessage());
        } catch (ImportException $ex) {
            $importAPITemplate->AddData("IMPORTRESULT", $ex->getMessage());
        } finally {
            $mainTemplate->AddData('INGREDIENTLIST', $desktopHtml);
            $mainTemplate->AddData('MOBILEINGREDIENTLIST', $mobileHtml);
            $assignIngredientTemplate->AddData('CHOOSECATEGORY', $ingredientListHtml);
            $removeIngredientTemplate->AddData('ASSIGNEDCATEGORYOPTIONS', $ingredientListHtml);
            $assignIngredientTemplate->AddData('RECIPELIST', $recipesHtml);
            $removeIngredientTemplate->AddData('RECIPELIST', $recipesHtml);
            $mainTemplate->AddData("STATUSOPTIONS", $statusHtml);
            $addIngredientTemplate->AddData('STATUSOPTIONS', $ingStatus);
            $editIngredientTemplate->AddData('STATUSOPTIONS', $ingStatus);

            $mainTemplate->AddData("ASSIGNCATEGORYMODAL", $assignIngredientTemplate);
            $mainTemplate->AddData("ADDCATEGORYMODAL", $addIngredientTemplate);
            $mainTemplate->AddData("IMORTAPIMODAL", $importAPITemplate);
            $mainTemplate->AddData("REMOVECATEGORYMODAL", $removeIngredientTemplate);
            $mainTemplate->AddData("EDITCATEGORYMODAL", $editIngredientTemplate);
            $mainTemplate->AddData("ERRORMODAL", $errorModal);
            $this->template->AddData("MAIN", $mainTemplate);
        }
    }
}