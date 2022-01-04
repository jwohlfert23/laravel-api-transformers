<?php namespace Laravel\SerializableClosure\Serializers;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
use Jwohlfert23\LaravelApiTransformers\Serializers\SerializerContract;

class DefaultSerializer implements SerializerContract
{
    public function item($data): array
    {
        return $data;
    }

    public function collection(Collection $collection): array
    {
        return [
            'data' => $collection->all()
        ];
    }

    public function paginator(Paginator $paginator): array
    {
        $currentPage = (int)$paginator->getCurrentPage();
        $lastPage = (int)$paginator->getLastPage();

        $pagination = [
            'total' => (int)$paginator->getTotal(),
            'count' => (int)$paginator->getCount(),
            'per_page' => (int)$paginator->getPerPage(),
            'current_page' => $currentPage,
            'total_pages' => $lastPage,
        ];

        $pagination['links'] = [];

        if ($currentPage > 1) {
            $pagination['links']['previous'] = $paginator->getUrl($currentPage - 1);
        }

        if ($currentPage < $lastPage) {
            $pagination['links']['next'] = $paginator->getUrl($currentPage + 1);
        }

        if (empty($pagination['links'])) {
            $pagination['links'] = (object)[];
        }

        return ['pagination' => $pagination];
    }
}
