# 坐标转换模块
本代码修改自[coordtransform](https://github.com/wandergis/coordtransform)，该库为坐标转换的js版本
此模块用于百度坐标系(bd-09)、火星坐标系(国测局坐标系、gcj02)、WGS84坐标系的相互转换。

其它语言版本的可移步[coordtransform](https://github.com/wandergis/coordtransform)查阅

# 使用说明
```
    $lng = 128.543
    $lat = 37.065
    $result1 = CoordTransform::bd09togcj02(lng, lat)#火星坐标系->百度坐标系
    $result2 = CoordTransform::gcj02tobd09(lng, lat)#百度坐标系->火星坐标系
    $result3 = CoordTransform::wgs84togcj02(lng, lat)#WGS84坐标系->火星坐标系
    $result4 = CoordTransform::gcj02towgs84(lng, lat)#火星坐标系->WGS84坐标系

```
