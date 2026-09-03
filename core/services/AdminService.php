<?php

final class AdminService
{
    public static function GetPendingRecipes(): array
    {
        $recipesModel = new RecipesModel();
        $pendingRecipes = [];

        $result = $recipesModel->GetRecipesByStatus("pending");
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $pendingRecipes[] = $row;
            }
        }

        return $pendingRecipes;
    }

    public static function GetActivityLog(): array
    {
        $activityLog = Container::Get("activityLog")->GetActivitiesWithUsername();
        $activites = [];

        if ($activityLog && $activityLog->num_rows > 0) {
            while ($row = $activityLog->fetch_assoc()) {
                $activites[] = $row;
            }
        }

        return $activites;
    }

    public static function GetCategoryChartData(int $limit): array
    {
        $model = new RecipesModel();

        $result = $model->GetRecipeCountsByCategory($limit);
        $categoryLabels = [];
        $categoryData = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $categoryLabels[] = $row['category_name'];
                $categoryData[] = (int) $row['recipe_count'];
            }
        }

        return [
            'labels' => $categoryLabels, 'data' => $categoryData
        ];
    }

    public static function GetUserUploadChartData(): array
    {
        $model = new RecipesModel();

        $topUsers = [];
        $result = $model->GetUploadsCountPerUser();
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $topUsers[] = [
                    'labels' => $row['username'],
                    'data' => (int) $row['recipe_count']
                ];
            }
        }

        return $topUsers;
    }

    public static function GetRelationChartData(string $relationType): array
    {
        if ($relationType !== 'favourite' && $relationType !== 'bookmark') {
            return [];
        }

        $model = new RecipesModel();
        
        $topRecipesByRelation = [];
        $result = $model->GetTopRecipesByRelation($relationType);
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $topRecipesByRelation[] = [
                    'labels' => $row['title'],
                    'data' => (int) $row['relation_count']
                ];
            }
        }

        return $topRecipesByRelation;
    }

}