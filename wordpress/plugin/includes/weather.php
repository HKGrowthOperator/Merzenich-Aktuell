<?php
if (!defined('ABSPATH')) { exit; }
function ma_register_weather_hooks(): void {
    add_action('rest_api_init', function () {
        register_rest_route('merzenich-aktuell/v1','/weather',['methods'=>'GET','callback'=>'ma_weather_rest','permission_callback'=>'__return_true']);
    });
}
function ma_weather_code_label(int $code): array {
    if ($code === 0) return ['☀️','Klar'];
    if (in_array($code,[1,2],true)) return ['🌤️','Heiter bis wolkig'];
    if ($code === 3) return ['☁️','Bewölkt'];
    if (in_array($code,[45,48],true)) return ['🌫️','Nebel'];
    if (in_array($code,[51,53,55,56,57],true)) return ['🌦️','Nieselregen'];
    if (in_array($code,[61,63,65,66,67,80,81,82],true)) return ['🌧️','Regen'];
    if (in_array($code,[71,73,75,77,85,86],true)) return ['🌨️','Schnee'];
    if (in_array($code,[95,96,99],true)) return ['⛈️','Gewitter'];
    return ['🌡️','Wetter'];
}
function ma_get_weather(bool $force=false): ?array {
    $s = wp_parse_args((array)get_option('ma_weather_settings',[]),['enabled'=>1,'latitude'=>'50.826813','longitude'=>'6.524935','cache_minutes'=>20]);
    if (empty($s['enabled'])) return null;
    $cache_key='ma_weather_current_v2';
    if (!$force && ($cached=get_transient($cache_key))) return $cached;
    $params = [
        'latitude'=>(float)$s['latitude'],'longitude'=>(float)$s['longitude'],'timezone'=>'Europe/Berlin',
        'current'=>'temperature_2m,weather_code,is_day',
        'daily'=>'weather_code,temperature_2m_max,temperature_2m_min,precipitation_probability_max',
        'forecast_days'=>3,
    ];
    $url = add_query_arg($params,'https://api.open-meteo.com/v1/forecast');
    $res = wp_remote_get($url,['timeout'=>5,'user-agent'=>'MerzenichAktuell/'.MA_CORE_VERSION]);
    if (!is_wp_error($res) && wp_remote_retrieve_response_code($res)===200) {
        $j=json_decode(wp_remote_retrieve_body($res),true);
        if (is_array($j) && isset($j['current']['temperature_2m'],$j['daily']['time'])) {
            $days=[];
            foreach ($j['daily']['time'] as $i=>$date) {
                [$icon,$label]=ma_weather_code_label((int)($j['daily']['weather_code'][$i]??-1));
                $days[]=['date'=>$date,'icon'=>$icon,'label'=>$label,'max'=>(int)round($j['daily']['temperature_2m_max'][$i]),'min'=>(int)round($j['daily']['temperature_2m_min'][$i]),'rain'=>(int)($j['daily']['precipitation_probability_max'][$i]??0)];
            }
            [$icon,$label]=ma_weather_code_label((int)$j['current']['weather_code']);
            $data=['temp'=>(int)round($j['current']['temperature_2m']),'icon'=>$icon,'label'=>$label,'days'=>$days,'updated_at'=>current_time('c'),'stale'=>false,'provider'=>'Open-Meteo'];
            set_transient($cache_key,$data,max(600,min(1800,((int)$s['cache_minutes'])*60)));
            update_option('ma_weather_last_good',$data,false);
            return $data;
        }
    }
    $last=(array)get_option('ma_weather_last_good',[]);
    if ($last) { $last['stale']=true; set_transient($cache_key,$last,300); return $last; }
    return null;
}
function ma_weather_rest(WP_REST_Request $request): WP_REST_Response { return new WP_REST_Response(ma_get_weather() ?: ['hidden'=>true],200); }
