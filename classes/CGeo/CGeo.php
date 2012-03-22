<?php
// Copyright (c) 2012 Tiinusen <tiinusen@wildgamers.net> 
// WildGamers Git here: https://github.com/Tiinusen/WildGamers
// 
// This is a modified version from Claus Jørgensen (Systembolaget Project)
// C# Project here: https://github.com/Windcape/Systembolaget/blob/master/Systembolaget.Core/Assets/GeoLocationHelper.cs
// 
// Copyright (c) 2011 Claus Jørgensen <thedeathart@gmail.com>
//
// Permission is hereby granted, free of charge, to any person obtaining a copy
// of this software and associated documentation files (the "Software"), to deal
// in the Software without restriction, including without limitation the rights
// to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
// copies of the Software, and to permit persons to whom the Software is
// furnished to do so, subject to the following conditions:
//
// The above copyright notice and this permission notice shall be included in
// all copies or substantial portions of the Software.
//
// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
// IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
// FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
// AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
// LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
// OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
// THE SOFTWARE

class RT90
{
    public $x;
    public $y;
    public function __construct($x, $y) {
        $this->x = $x;
        $this->y = $y;
    }
    
    public function __toString() {
        return "X: ".$this->x.", Y:".$this->y;
    }
    
    /**
     * Creates a WGS84 Object
     * @return WGS84
     */
    public function toWGS84() {
        return CGeo::ToWGS84($this->x, $this->y);
    }
}

class WGS84
{
    public $latitude;
    public $longitude;
    public function __construct($latitude, $longitude) {
        $this->latitude = $latitude;
        $this->longitude = $longitude;
    }
    
    public function __toString() {
        return "Lat:".$this->latitude.", Long:".$this->longitude;
    }
    
    /**
     * Creates a WGS84 Object
     * @return RT90
     */
    public function toRT90() {
        return CGeo::ToRT90($this->latitude, $this->longitude);
    }
}



class CGeo {

    /**
     *
     * @param int $x
     * @param int $y
     * @return WGS84 
     */
    public static function ToWGS84($x, $y) {
        $axis = 6378137.0;
        $flattening = 1.0 / 298.257222101;
        $centralMeridian = 15.0 + 48.0 / 60.0 + 22.624306 / 3600.0;
        $scale = 1.00000561024;
        $falseNorthing = -667.711;
        $falseEasting = 1500064.274;
        return CGeo::GridToGeodetic($x, $y, $axis, $flattening, $centralMeridian, $scale, $falseNorthing, $falseEasting);
    }

    /**
     *
     * @param int $latitude
     * @param int $longitude
     * @return RT90 
     */
    public static function ToRT90($latitude, $longitude) {
        $axis = 6378137.0;
        $flattening = 1.0 / 298.257222101;
        $centralMeridian = 15.0 + 48.0 / 60.0 + 22.624306 / 3600.0;
        $scale = 1.00000561024;
        $falseNorthing = -667.711;
        $falseEasting = 1500064.274;

        return CGeo::GeodeticToGrid($latitude, $longitude, $axis, $flattening, $centralMeridian, $scale, $falseNorthing, $falseEasting);
    }

    /**
     *
     * @param type $x
     * @param type $y
     * @param type $axis
     * @param type $flattening
     * @param type $centralMeridian
     * @param type $scale
     * @param type $falseNorthing
     * @param type $falseEasting
     * @return WGS84 
     */
    private static function GridToGeodetic($x, $y, $axis, $flattening, $centralMeridian, $scale, $falseNorthing, $falseEasting) {
        $e2 = $flattening * (2.0 - $flattening);
        $n = $flattening / (2.0 - $flattening);
        $a_roof = $axis / (1.0 + $n) * (1.0 + $n * $n / 4.0 + $n * $n * $n * $n / 64.0);
        $delta1 = $n / 2.0 - 2.0 * $n * $n / 3.0 + 37.0 * $n * $n * $n / 96.0 - $n * $n * $n * $n / 360.0;
        $delta2 = $n * $n / 48.0 + $n * $n * $n / 15.0 - 437.0 * $n * $n * $n * $n / 1440.0;
        $delta3 = 17.0 * $n * $n * $n / 480.0 - 37 * $n * $n * $n * $n / 840.0;
        $delta4 = 4397.0 * $n * $n * $n * $n / 161280.0;

        $Astar = $e2 + $e2 * $e2 + $e2 * $e2 * $e2 + $e2 * $e2 * $e2 * $e2;
        $Bstar = -(7.0 * $e2 * $e2 + 17.0 * $e2 * $e2 * $e2 + 30.0 * $e2 * $e2 * $e2 * $e2) / 6.0;
        $Cstar = (224.0 * $e2 * $e2 * $e2 + 889.0 * $e2 * $e2 * $e2 * $e2) / 120.0;
        $Dstar = -(4279.0 * $e2 * $e2 * $e2 * $e2) / 1260.0;

        $deg_to_rad = pi() / 180;
        $lambda_zero = $centralMeridian * $deg_to_rad;
        $xi = ($x - $falseNorthing) / ($scale * $a_roof);
        $eta = ($y - $falseEasting) / ($scale * $a_roof);

        $xi_prim = $xi -
                $delta1 * sin(2.0 * $xi) * cosh(2.0 * $eta) -
                $delta2 * sin(4.0 * $xi) * cosh(4.0 * $eta) -
                $delta3 * sin(6.0 * $xi) * cosh(6.0 * $eta) -
                $delta4 * sin(8.0 * $xi) * cosh(8.0 * $eta);

        $eta_prim = $eta -
                $delta1 * cos(2.0 * $xi) * sinh(2.0 * $eta) -
                $delta2 * cos(4.0 * $xi) * sinh(4.0 * $eta) -
                $delta3 * cos(6.0 * $xi) * sinh(6.0 * $eta) -
                $delta4 * cos(8.0 * $xi) * sinh(8.0 * $eta);

        $phi_star = asin(sin($xi_prim) / cosh($eta_prim));
        $delta_lambda = atan(sinh($eta_prim) / cos($xi_prim));
        $lon_radian = $lambda_zero + $delta_lambda;

        $lat_radian = $phi_star + sin($phi_star) * cos($phi_star) *
                ($Astar +
                $Bstar * pow(sin($phi_star), 2) +
                $Cstar * pow(sin($phi_star), 4) +
                $Dstar * pow(sin($phi_star), 6));

        $newLatitude = $lat_radian * 180.0 / pi();
        $newLongitude = $lon_radian * 180.0 / pi();

        return new WGS84($newLatitude, $newLongitude);
    }

    /**
     *
     * @param type $latitude
     * @param type $longitude
     * @param type $axis
     * @param type $flattening
     * @param type $centralMeridian
     * @param type $scale
     * @param type $falseNorthing
     * @param type $falseEasting
     * @return RT90 
     */
    private static function GeodeticToGrid($latitude, $longitude, $axis, $flattening, $centralMeridian, $scale, $falseNorthing, $falseEasting) {
        $e2 = $flattening * (2.0 - $flattening);
        $n = $flattening / (2.0 - $flattening);
        $a_roof = $axis / (1.0 + $n) * (1.0 + $n * $n / 4.0 + $n * $n * $n * $n / 64.0);
        $A = $e2;
        $B = (5.0 * $e2 * $e2 - $e2 * $e2 * $e2) / 6.0;
        $C = (104.0 * $e2 * $e2 * $e2 - 45.0 * $e2 * $e2 * $e2 * $e2) / 120.0;
        $D = (1237.0 * $e2 * $e2 * $e2 * $e2) / 1260.0;
        $beta1 = $n / 2.0 - 2.0 * $n * $n / 3.0 + 5.0 * $n * $n * $n / 16.0 + 41.0 * $n * $n * $n * $n / 180.0;
        $beta2 = 13.0 * $n * $n / 48.0 - 3.0 * $n * $n * $n / 5.0 + 557.0 * $n * $n * $n * $n / 1440.0;
        $beta3 = 61.0 * $n * $n * $n / 240.0 - 103.0 * $n * $n * $n * $n / 140.0;
        $beta4 = 49561.0 * $n * $n * $n * $n / 161280.0;

        $deg_to_rad = pi() / 180.0;
        $phi = $latitude * $deg_to_rad;
        $lambda = $longitude * $deg_to_rad;
        $lambda_zero = $centralMeridian * $deg_to_rad;

        $phi_star = $phi - sin($phi) * cos($phi)
                * ($A + $B * pow(sin($phi), 2)
                + $C * pow(sin($phi), 4)
                + $D * pow(sin($phi), 6));

        $delta_lambda = $lambda - $lambda_zero;
        $xi_prim = atan(tan($phi_star) / cos($delta_lambda));
        $eta_prim = atanh(cos($phi_star) * sin($delta_lambda));

        $x = $scale * $a_roof * ($xi_prim +
                $beta1 * sin(2.0 * $xi_prim) * cosh(2.0 * $eta_prim) +
                $beta2 * sin(4.0 * $xi_prim) * cosh(4.0 * $eta_prim) +
                $beta3 * sin(6.0 * $xi_prim) * cosh(6.0 * $eta_prim) +
                $beta4 * sin(8.0 * $xi_prim) * cosh(8.0 * $eta_prim)) +
                $falseNorthing;

        $y = $scale * $a_roof * ($eta_prim +
                $beta1 * cos(2.0 * $xi_prim) * sinh(2.0 * $eta_prim) +
                $beta2 * cos(4.0 * $xi_prim) * sinh(4.0 * $eta_prim) +
                $beta3 * cos(6.0 * $xi_prim) * sinh(6.0 * $eta_prim) +
                $beta4 * cos(8.0 * $xi_prim) * sinh(8.0 * $eta_prim)) +
                $falseEasting;

        $newX = round($x * 1000.0) / 1000.0;
        $newY = round($y * 1000.0) / 1000.0;

        return new RT90($newX, $newY);
    }

}

?>
