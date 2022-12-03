<?php
// El obetivo del juguete es insertar figuras geometricas en los orificios que contiene a los lados.
//Al tratar de insertar una figura en un orifcio tenemos dos posibles resultados:
//1.- entro: verdadero
//2.- no entro: false
// para saber si una figura entra en el orificio, requerimos saber dos cosas:
//1.- El area de la figura que queremos insertar
//2.- El area del orificio donde queremos insertar la figura
class Shape_0
{

    protected $areas = [];

    public function insert(Shape $shape,$hole) : bool
    {
        
        return $shape->area() <= $this->areas[$hole];
    }
}

abstract class Shape
{
    abstract public function area();
    
}

class Circle
{
    public function area()
    {
        return M_PI * 5 ^ 2;
    }
}

class Square 
{
    public function area()
    {
        return 5 * 5;
    }
}