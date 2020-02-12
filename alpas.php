<?php

use Alfred\Workflows\Workflow;
use AlgoliaSearch\Client as Algolia;
use AlgoliaSearch\Version as AlgoliaUserAgent;

require __DIR__.'/vendor/autoload.php';

$query = $argv[1];

$workflow = new Workflow;
$parsedown = new Parsedown;
$algolia = new Algolia('BH4D9OD16A', 'e017bb0b99fe3193f750d48a4e7d441e');

AlgoliaUserAgent::addSuffixUserAgentSegment('Alpas Docs Alfred Workflow', '1.0.1');

$index = $algolia->initIndex('alpas');
$search = $index->search($query);
$results = $search['hits'];

if (empty($results)) {
    $workflow->result()
             ->title('No matches')
             ->icon('google.png')
             ->subtitle("No match found in the docs. Search Google for: \"Alpas+{$query}\"")
             ->arg("https://www.google.com/search?q=alpas+{$query}")
             ->quicklookurl("https://www.google.com/search?q=alpas+{$query}")
             ->valid(true);

    echo $workflow->output();
    exit;
}

$urls = [];


foreach ($results as $hit) {
    $highestLvl = $hit['hierarchy']['lvl6'] ? 6 : ($hit['hierarchy']['lvl5'] ? 5 : ($hit['hierarchy']['lvl4'] ? 4 : ($hit['hierarchy']['lvl3'] ? 3 : ($hit['hierarchy']['lvl2'] ? 2 : ($hit['hierarchy']['lvl1'] ? 1 : 0)))));

    $title = $hit['hierarchy']['lvl'.$highestLvl];
    $currentLvl = 0;
    $subtitle = $hit['hierarchy']['lvl0'];
    while ($currentLvl < $highestLvl) {
        $currentLvl = $currentLvl + 1;
        $lvl = $hit['hierarchy']['lvl'.$currentLvl];
        if (!empty($lvl)) {
            $subtitle = $subtitle.' ⇝ '.$lvl;
        }
    }

    $workflow->result()
             ->uid($hit['objectID'])
             ->title($title)
             ->autocomplete($title)
             ->subtitle($subtitle)
             ->arg($hit['url'])
             ->quicklookurl($hit['url'])
             ->valid(true);
}

echo $workflow->output();
