<?php

namespace App\Service\Pagination;

class PaginationLinks
{
    /**
     * Генерирует ссылки для пагинации
     *
     * @param int $totalPages
     * @param int $currentPage
     * @param string $route
     * @param array $queryParams
     * @return string
     */
    public function generateLinks(int $totalPages, int $currentPage, string $route, array $queryParams = []): string
    {
        if ($totalPages <= 1) {
            return ''; // Если только одна страница, пагинацию отображать не нужно
        }

        $paginationHtml = '<div class="pagination">';

        // Кнопка "Назад"
        if ($currentPage > 1) {
            $paginationHtml .= $this->generateLink($currentPage - 1, 'Назад', $route, $queryParams, 'prev link-text');
        }

        // Страницы 1-4
        for ($page = 1; $page <= min(4, $totalPages); $page++) {
            $activeClass = ($page === $currentPage) ? 'link active' : 'link';
            $paginationHtml .= $this->generateLink($page, (string) $page, $route, $queryParams, $activeClass);
        }

        // Добавить многоточие, если на странице 5 и более
        if ($ellipseBefore = ($totalPages > 4 && $currentPage > 4)) {
            $paginationHtml .= '<span class="ellipse">...</span>';
        }

        // Добавить текущую страницу и пару соседних, если мы перейдем к странице 4
        $startPage = max(5, $currentPage);
        $endPage = min($currentPage + 1, $totalPages - 1);  // Stop one before the last page

        for ($page = $startPage; $page <= $endPage; $page++) {
            $activeClass = ($page === $currentPage) ? 'link active' : 'link';
            $paginationHtml .= $this->generateLink($page, (string) $page, $route, $queryParams, $activeClass);
        }

        // Добавить многоточие перед последней страницей
        if ($ellipseAfter = ($endPage < $totalPages - 1 && $totalPages > 4)) {
            $paginationHtml .= '<span>...</span>';
        }

        // Ссылка на последнюю страницу
        if ($totalPages > 4 && ($ellipseBefore || $ellipseAfter)) {
            $activeClass = ($totalPages === $currentPage) ? 'link active' : 'link';
            $paginationHtml .= $this->generateLink($totalPages, (string) $totalPages, $route, $queryParams, $activeClass);
        }

        // Кнопка "Далее"
        if ($currentPage < $totalPages) {
            $paginationHtml .= $this->generateLink($currentPage + 1, 'Далее', $route, $queryParams, 'next link-text');
        }

        $paginationHtml .= '</div>';

        return $paginationHtml;
    }

    /**
     * Вспомогательная функция для создания ссылок для пагинации
     *
     * @param int $page
     * @param string $label
     * @param string $route
     * @param array $queryParams
     * @param string $activeClass
     * @return string
     */
    private function generateLink(int $page, string $label, string $route, array $queryParams, string $activeClass = ''): string
    {
        $queryParams['page'] = $page;
        $url = $route . '?' . http_build_query($queryParams);
        return sprintf('<a href="%s" class="%s">%s</a>', $url, $activeClass, $label);
    }
}