<?php

namespace App\Utils;

use Illuminate\Support\Str;
use Illuminate\Pagination\LengthAwarePaginator;


class SEOFriendlyPaginator extends LengthAwarePaginator {

    public function url($page)
    {
        if ($page <= 0) {
            $page = 1;
        }

        $parameters = array();
        if ($page > 1) {
            $parameters = [$this->pageName => $page];
        }

        if (count($this->query) > 0) {
            $parameters = array_merge($this->query, $parameters);
        }

        $url = $this->path;

        $params_delimiter = (Str::contains($this->path, '?') ? '&' : '?');
        $params_query = http_build_query($parameters, '', '&');
        $fragment = $this->buildFragment();

        if (!empty($params_query)) {
            $url .= $params_delimiter . $params_query;
        }
        if (!empty($fragment)) {
            $url .= $fragment;
        }

        return $url;
    }
}