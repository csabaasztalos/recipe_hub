<?php
final class FilterService
{
    public static function GetFilterOptions(): array
    {
        try {
            $categories = CategoryService::GetCategoriesByStatus("approved");
            $tagsList = TagsService::GetTagsByStatus("approved");
            return [
                $categories,
                $tagsList
            ];
        } catch (Exception $ex) {
            throw new FilterServiceException("Failed to get filter options.");
        }
    }

    public static function ParseFilter($get): array
    {
        $filters = [];
        if (isset($get['filter'])) {
            $tags = [];
            $tagInputs = $get['tags'] ?? [];
            if (! empty($tagInputs['difTag'])) {
                $tags[] = (int) $tagInputs['difTag'];
            }
            if (! empty($tagInputs['timeTag'])) {
                $tags[] = (int) $tagInputs['timeTag'];
            }
            if (! empty($tagInputs['budgetTag'])) {
                $tags[] = (int) $tagInputs['budgetTag'];
            }
            if (! empty($tagInputs['occTag'])) {
                $tags[] = (int) $tagInputs['occTag'];
            }
            if (! empty($tagInputs['methodTag'])) {
                $tags[] = (int) $tagInputs['methodTag'];
            }
            if (! empty($tagInputs['styleTag'])) {
                $tags[] = (int) $tagInputs['styleTag'];
            }
            $tags = array_filter($tags, function ($v) {
                return ! empty($v);
            });

            $filters = [
                'keyword' => $get['keyword'],
                'category' => $get['category'],
                'tags' => $tags,
                'sortBy' => $get['sortBy'],
                'order' => $get['order'],
                'limit' => "",
                'userID' => "",
                'relation' => "",
                'recipeID' => ""
            ];
        }

        return $filters;
    }

    public static function ResetFilter(): void
    {
        global $cfg;
        header("Location: {$cfg['recipesPage']}");
        exit();
    }
}