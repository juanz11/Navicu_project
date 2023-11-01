<?php

namespace AleksandrNovoselov\Robots\Classes;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Collection;

class BotsAndCrawlers
{
    protected const CACHE_KEY = 'user-agents-for-robots';
    protected const UNIQUE = [
        'trendictionbot',
        'AppleWebKit/',
        'SimplePie/',
        'DomainAppender',
        'BoardReader',
        'GoogleAuth',
        'Sogou',
    ];

    public static function getUserAgentsForRobots(): array
    {
        if ($userAgents = \Cache::get(self::CACHE_KEY)) {
            return $userAgents;
        }

        $client = new Client();
        $foundUserAgents = Collection::make();
        Collection::make(
            json_decode(
                $client->post('https://user-agents.net/download', [
                    RequestOptions::FORM_PARAMS => [
                        'browser_type' => 'bot-crawler',
                        'download'     => 'json',
                    ],
                ])->getBody()->getContents(),
                true
            )
        )->each(function(string $userAgent, int $index) use($foundUserAgents): void {
            foreach (self::UNIQUE as $uniqueAgent) {
                if (strpos($userAgent, $uniqueAgent) !== false) {
                    self::pushUserAgent($uniqueAgent, $foundUserAgents);

                    return;
                }
            }

            $splitUserAgent = Collection::make(preg_split('/( |;|\(|\))/', $userAgent))->map(function (string &$agent) {
                return trim(
                    preg_replace_callback(
                        "(\\\\x([0-9a-f]{2}))i",
                        function($a) {
                            return chr(hexdec($a[1]));
                        },
                        $agent
                    ),
                    "\""
                );
            });
            if ($splitUserAgent->count() === 1) {
                self::pushUserAgent($splitUserAgent->first(), $foundUserAgents);

                return;
            }

            $foundUserAgent = $splitUserAgent->filter(function (string $agent) {
                return strlen($agent)
                       && strpos(strtolower($agent), 'bot') !== false
                       && strpos($agent, '|') === false
                       && filter_var($agent, FILTER_VALIDATE_URL) === false
                       && filter_var($agent, FILTER_VALIDATE_EMAIL) === false
                       && !\Str::startsWith($agent, '+')
                    ;
            });
            if ($foundUserAgent->count() === 1) {
                self::pushUserAgent($splitUserAgent->first(), $foundUserAgents);

                return;
            }

            $foundUserAgent = $splitUserAgent->filter(function (string $agent) {
                return strlen($agent)
                       && strtolower($agent) !== 'compatible'
                       && strtolower($agent) !== 'crawler'
                       && strtolower($agent) !== 'mozilla/5.0'
                       && strpos($agent, '|') === false
                       && filter_var($agent, FILTER_VALIDATE_URL) === false
                       && filter_var($agent, FILTER_VALIDATE_EMAIL) === false
                       && !\Str::startsWith($agent, '+')
                    ;
            });
            if ($foundUserAgent->count() === 1) {
                self::pushUserAgent($splitUserAgent->first(), $foundUserAgents);

                return;
            }

            $foundUserAgent = $foundUserAgent->filter(function (string $agent) {
                return strpos($agent, '/') !== false;
            });
            if ($foundUserAgent->count() === 1) {
                self::pushUserAgent($splitUserAgent->first(), $foundUserAgents);

                return;
            }

            $lowerUserAgent = strtolower($userAgent);
            foreach ($foundUserAgents->keys() as $lowerAgent) {
                if (strpos($lowerUserAgent, (string)$lowerAgent) !== false) {
                    return;
                }
            }
        });
        \Cache::put(self::CACHE_KEY, $foundUserAgents->all(), \Date::now()->addMonth());

        return $foundUserAgents->all();
    }

    protected static function pushUserAgent(string $agent, Collection $foundUserAgents): void
    {
        if (strpos($agent, '<') !== false) {
            return;
        }

        if (strpos($agent, '/') !== false) {
            $agent = explode('/', $agent);
            array_pop($agent);
            $agent = implode('/', $agent);
        }

        if (!empty($agent) && !$foundUserAgents->has($lowerAgent = strtolower($agent))) {
            $foundUserAgents->put($lowerAgent, $agent);
        }
    }

}
