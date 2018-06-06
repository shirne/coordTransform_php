<?php


/**
 * Class Transform
 * 提供了百度坐标（BD09）、国测局坐标（火星坐标，GCJ02）、和WGS84坐标系之间的转换
 * 修改自[coordtransform](https://github.com/wandergis/coordtransform)
 */
class CoordTransform
{
    const X_PI = 52.35987755983;
    const PI = 3.1415926535897932384626;
    const A = 6378245.0;
    const EE = 0.00669342162296594323;

    /**
     * 百度坐标系 (BD-09) 与 火星坐标系 (GCJ-02)的转换
     * 即 百度 转 谷歌、高德
     * @param bd_lon
     * @param bd_lat
     * @returns array
     */
    public static function bd09togcj02($bd_lon, $bd_lat)
    {
        $bd_lon = +$bd_lon;
        $bd_lat = +$bd_lat;
        $x = $bd_lon - 0.0065;
        $y = $bd_lat - 0.006;
        $z = sqrt($x * $x + $y * $y) - 0.00002 * sin($y * self::X_PI);
        $theta = atan2($y, $x) - 0.000003 * cos($x * self::X_PI);
        $gg_lng = $z * cos($theta);
        $gg_lat = $z * sin($theta);
        return array($gg_lng, $gg_lat);
    }

    /**
     * 火星坐标系 (GCJ-02) 与百度坐标系 (BD-09) 的转换
     * 即谷歌、高德 转 百度
     * @param lng
     * @param lat
     * @returns array
     */
    public static function gcj02tobd09($lng, $lat)
    {
        $lat = +$lat;
        $lng = +$lng;
        $z = sqrt($lng * $lng + $lat * $lat) + 0.00002 * sin($lat * self::X_PI);
        $theta = atan2($lat, $lng) + 0.000003 * cos($lng * self::X_PI);
        $bd_lng = $z * cos($theta) + 0.0065;
        $bd_lat = $z * sin($theta) + 0.006;
        return array($bd_lng, $bd_lat);
    }

    /**
     * WGS84转GCj02
     * @param lng
     * @param lat
     * @returns array
     */
    public static function wgs84togcj02($lng, $lat)
    {
        $lat = +$lat;
        $lng = +$lng;
        if (self::out_of_china($lng, $lat)) {
            return array($lng, $lat);
        } else {
            $dlat = self::transformlat($lng - 105.0, $lat - 35.0);
            $dlng = self::transformlng($lng - 105.0, $lat - 35.0);
            $radlat = $lat / 180.0 * self::PI;
            $magic = sin($radlat);
            $magic = 1 - self::EE * $magic * $magic;
            $sqrtmagic = sqrt($magic);
            $dlat = ($dlat * 180.0) / ((self::A * (1 - self::EE)) / ($magic * $sqrtmagic) * self::PI);
            $dlng = ($dlng * 180.0) / (self::A / $sqrtmagic * cos($radlat) * self::PI);
            $mglat = $lat + $dlat;
            $mglng = $lng + $dlng;
            return array($mglng, $mglat);
        }
    }

    /**
     * GCJ02 转换为 WGS84
     * @param lng
     * @param lat
     * @returns array
     */
    public static function gcj02towgs84($lng, $lat)
    {
        $lat = +$lat;
        $lng = +$lng;
        if (self::out_of_china($lng, $lat)) {
            return array($lng, $lat);
        } else {
            $dlat = self::transformlat($lng - 105.0, $lat - 35.0);
            $dlng = self::transformlng($lng - 105.0, $lat - 35.0);
            $radlat = $lat / 180.0 * self::PI;
            $magic = sin($radlat);
            $magic = 1 - self::EE * $magic * $magic;
            $sqrtmagic = sqrt($magic);
            $dlat = ($dlat * 180.0) / ((self::A * (1 - self::EE)) / ($magic * $sqrtmagic) * self::PI);
            $dlng = ($dlng * 180.0) / (self::A / $sqrtmagic * cos($radlat) * self::PI);
            $mglat = $lat + $dlat;
            $mglng = $lng + $dlng;
            return array($lng * 2 - $mglng, $lat * 2 - $mglat);
        }
    }

    public static function transformlat($lng, $lat)
    {
        $lat = +$lat;
        $lng = +$lng;
        $ret = -100.0 + 2.0 * $lng + 3.0 * $lat + 0.2 * $lat * $lat + 0.1 * $lng * $lat + 0.2 * sqrt(abs($lng));
        $ret += (20.0 * sin(6.0 * $lng * self::PI) + 20.0 * sin(2.0 * $lng * self::PI)) * 2.0 / 3.0;
        $ret += (20.0 * sin($lat * self::PI) + 40.0 * sin($lat / 3.0 * self::PI)) * 2.0 / 3.0;
        $ret += (160.0 * sin($lat / 12.0 * self::PI) + 320 * sin($lat * self::PI / 30.0)) * 2.0 / 3.0;
        return $ret;
    }

    public static function transformlng($lng, $lat)
    {
        $lat = +$lat;
        $lng = +$lng;
        $ret = 300.0 + $lng + 2.0 * $lat + 0.1 * $lng * $lng + 0.1 * $lng * $lat + 0.1 * sqrt(abs($lng));
        $ret += (20.0 * sin(6.0 * $lng * self::PI) + 20.0 * sin(2.0 * $lng * self::PI)) * 2.0 / 3.0;
        $ret += (20.0 * sin($lng * self::PI) + 40.0 * sin($lng / 3.0 * self::PI)) * 2.0 / 3.0;
        $ret += (150.0 * sin($lng / 12.0 * self::PI) + 300.0 * sin($lng / 30.0 * self::PI)) * 2.0 / 3.0;
        return $ret;
    }

    /**
     * 判断是否在国内，不在国内则不做偏移
     * @param lng
     * @param lat
     * @returns boolean
     */
    public static function out_of_china($lng, $lat)
    {
        $lat = +$lat;
        $lng = +$lng;
        // 纬度3.86~53.55,经度73.66~135.05
        return !($lng > 73.66 && $lng < 135.05 && $lat > 3.86 && $lat < 53.55);
    }

}