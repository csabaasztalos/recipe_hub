<?php
class AdminController implements IPageBase
{

    private Template $template;

    private Model $model;
    private ActivityLogModel $activityLog;

    public function __construct()
    {
        $this->model = Container::Get("model");
        $this->activityLog = Container::Get("activityLog");
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

            //sidebar
            $this->template->AddData('TITLE', $this->model->LoadContent($pageData['pageID'], "TITLE")["content"]);
            $this->template->AddData('HOME', $this->model->LoadContent($pageData['pageID'], "HOME")["content"]);
            $this->template->AddData('RECIPES', $this->model->LoadContent($pageData['pageID'], "RECIPES")["content"]);
            $this->template->AddData('CATEGORIES', $this->model->LoadContent($pageData['pageID'], "CATEGORIES")["content"]);
            $this->template->AddData('INGREDIENTS', $this->model->LoadContent($pageData['pageID'], "INGREDIENTS")["content"]);
            $this->template->AddData('USERS', $this->model->LoadContent($pageData['pageID'], "USERS")["content"]);
            $this->template->AddData('LOGOUT', $this->model->LoadContent($pageData['pageID'], "LOGOUT")["content"]);
            $mainTemplate = Template::Load("admin/dashboard.html");
            $mainTemplate->AddData('RECENTACTIVITY', $this->model->LoadContent($pageData['pageID'], "RECENTACTIVITY")["content"]);
            $mainTemplate->AddData('HOME', $this->model->LoadContent($pageData['pageID'], "HOME")["content"]);
            $mainTemplate->AddData('ALLRECIPES', $this->model->LoadContent($pageData['pageID'], "ALLRECIPES")["content"]);
            $mainTemplate->AddData('RECIPESLINK', $this->model->LoadContent($pageData['pageID'], "RECIPESLINK")["content"]);
            $mainTemplate->AddData('PENDINGRECIPES', $this->model->LoadContent($pageData['pageID'], "PENDINGRECIPES")["content"]);
            $mainTemplate->AddData('PENDINGRECIPESLINK', $this->model->LoadContent($pageData['pageID'], "PENDINGRECIPESLINK")["content"]);
            $mainTemplate->AddData('TOTALCATEGORIES', $this->model->LoadContent($pageData['pageID'], "TOTALCATEGORIES")["content"]);
            $mainTemplate->AddData('CATEGORIESLINK', $this->model->LoadContent($pageData['pageID'], "CATEGORIESLINK")["content"]);
            $mainTemplate->AddData('TOTALUSERS', $this->model->LoadContent($pageData['pageID'], "TOTALUSERS")["content"]);
            $mainTemplate->AddData('USERSLINK', $this->model->LoadContent($pageData['pageID'], "USERSLINK")["content"]);

            $recipesModel = new RecipesModel();
            $categoriesModel = new CategoriesModel();
            $usersModel = new UsersModel();

            $totalRecipes = $recipesModel->GetAllRecipes()->num_rows;
            $mainTemplate->AddData('ALLRECIPESCOUNT', $totalRecipes);

            $totalPending = $recipesModel->GetRecipesByStatus("pending")->num_rows;
            $mainTemplate->AddData('PENDINGRECIPESCOUNT', $totalPending);

            $categories = $categoriesModel->GetAllCategories()->num_rows;
            $mainTemplate->AddData('CATEGORIESCOUNT', $categories);

            $totalUsers = $usersModel->GetAllUsers()->num_rows;
            $mainTemplate->AddData('USERSCOUNT', $totalUsers);

            //pending recipes
            $pendingRecipes = AdminService::GetPendingRecipes();
            $mainTemplateData = AdminView::DrawPendingTable($pendingRecipes);
            foreach ($mainTemplateData as $key => $value) {
                $mainTemplate->AddData($key, $value);
            }

            //activity log
            $activies = AdminService::GetActivityLog();
            $acitivyTableData = AdminView::DrawActivityTable($activies);
            $mainTemplate->AddData('ACTIVITYLIST', $acitivyTableData);
            
            //charts
            $catData = AdminService::GetCategoryChartData(5);
            $userData = AdminService::GetUserUploadChartData();
            $likeData = AdminService::GetRelationChartData('favourite');
            $bookmarkData = AdminService::GetRelationChartData('bookmark');

            $chartInputs = array_merge(
                AdminView::GenerateCatChart($catData),
                AdminView::GenerateUserChart($userData),
                AdminView::GenerateRelationChart($likeData, 'favourite'),
                AdminView::GenerateRelationChart($bookmarkData, 'bookmark')
            );
            $hiddenInputsHtml = implode('', array_values($chartInputs));

            $mainTemplate->AddData("HIDDENINPUTS", $hiddenInputsHtml);
            $this->template->AddData("MAIN", $mainTemplate);

            if (isset($_POST['logout'])) {
                Logout::DestroySession();
            }

        } catch (Exception $ex) {
            //TODO:: hibakezeles
        }
    }
}
