<?php

/**
 * MIT License
 * ===========
 *
 * Copyright (c) 2012 Ing. Mauricio Lopez <mlopez@dixian.info>
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be included
 * in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
 * IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
 * CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
 * TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
 * SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 * @package     ValidarIdentificacion
 * @subpackage  
 * @author      Ing. Mauricio Lopez <mlopez@dixian.info>
 * @copyright   2012 Ing. Mauricio Lopez (diaspar)
 * @license     http://www.opensource.org/licenses/mit-license.php  MIT License
 * @link        http://www.dixian.info
 * @version     @@0.7@@
 */

/**
 * ValidarIdentificacion contiene metodos para validar cédula, RUC de persona natural/juridica y
 * RUC de empresas públicas en el Ecuador.
 */
class ValidarIdentificacion
{

    /**
     * @access protected
     * @var string
     */
    protected $error = '';

    function __construct()
    {
        // nothing.
    }

    /**
     * Validar cédula
     * 
     * @param  string  $value  Número de cédula
     * 
     * @return Boolean      
     */
    public function validarCedula($numero = '')
    {   
        // fuerzo parametro de entrada a string
        $numero = (string)$numero;

        // borro por si acaso errores de llamadas anteriores.
        $this->setError('');

        // validaciones
        try {
            $this->validarInicial($numero, '10');
            $this->validarCodigoProvincia(substr($numero, 0, 2));
            $this->validarTercerDigito($numero[2]);
            $this->algoritmoModulo10(substr($numero, 0, 9), $numero[9]);
        } catch (Exception $e) {
            $this->setError($e->getMessage());
            return false; 
        }

        return true;
    }
   
    /**
     * Validar RUC persona natural
     * 
     * @param  string  $value  Número de RUC persona natural
     * 
     * @return Boolean
     */
    public function validarRucPersonaNatural($numero = '')
    {
        // fuerzo parametro de entrada a string
        $numero = (string)$numero;

        // borro por si acaso errores de llamadas anteriores.
        $this->setError('');

        // validaciones
        try {
            $this->validarInicial($numero, '13');
            $this->validarCodigoProvincia(substr($numero, 0, 2));
            $this->validarTercerDigito($numero[2]);
            $this->validarCodigoEstablecimiento(substr($numero, 10, 3));
            $this->algoritmoModulo10(substr($numero, 0, 9), $numero[9]);
        } catch (Exception $e) {
            $this->setError($e->getMessage());
            return false; 
        }

        return true;
    }
   
    /**
     * Validaciones iniciales para CI y RUC
     * 
     * @param  string  $numero      CI o RUC
     * @param  integer $caracteres  Cantidad de caracteres requeridos
     * 
     * @return Boolean
     * 
     * @throws exception Cuando valor esta vacio, cuando no es dígito y 
     * cuando no tiene cantidad requerida de caracteres
     */
    protected function validarInicial($numero, $caracteres)
    {   
        if (empty($numero)) {
            throw new Exception('Valor no puede estar vacio');
        }
        
        if (!ctype_digit($numero)) {
            throw new Exception('Valor ingresado solo puede tener dígitos');
        }

        if (strlen($numero) != $caracteres) {
            throw new Exception('Valor ingresado debe tener '.$caracteres.' caracteres');
        }

        return true;
    }

    /**
     * Validación de código de provincia (dos primeros dígitos de CI/RUC)
     * 
     * @param  string  $numero  Dos primeros dígitos de CI/RUC
     * 
     * @return boolean
     *
     * @throws exception Cuando el código de provincia no esta entre 00 y 24
     */
    protected function validarCodigoProvincia($numero)
    {
        if ($numero < 0 OR $numero > 24) {
            throw new Exception('Codigo de Provincia (dos primeros dígitos) no deben ser mayor a 24 ni menores a 0');
        }

        return true;
    }
    
    /**
     * Validación de tercer dígito
     * 
     * @param  string $numero  tercer dígito de CI/RUC
     * 
     * @return boolean
     *
     * @throws exception Cuando el tercer digito no es 0,1,2,3,4,5
     */
    protected function validarTercerDigito($numero)
    {
        if ($numero < 0 OR $numero > 5) {
            throw new Exception('Tercer dígito debe ser mayor o igual a 0 y menor a 6');
        }

        return true;
    }

    /**
     * Validación de código de establecimiento
     * 
     * @param  string $numero  tercer dígito de CI/RUC
     * 
     * @return boolean
     *
     * @throws exception Cuando el establecimiento es menor a 1
     */
    protected function validarCodigoEstablecimiento($numero)
    {
        if ($numero < 1) {
            throw new Exception('Código de establecimiento no puede ser 0');
        }

        return true;
    }

    /**
     * Algoritmo Modulo10 para validar si CI y RUC de persona natural son válidos.
     *
     * Los coeficientes usados para verificar el décimo dígito de la cédula, 
     * mediante el algoritmo “Módulo 10” son:  2. 1. 2. 1. 2. 1. 2. 1. 2
     *
     * Paso 1: Multiplicar cada dígito de los digitosIniciales por su respectivo 
     * coeficiente.
     * 
     *  Ejemplo
     *  digitosIniciales posicion 1  x 2
     *  digitosIniciales posicion 2  x 1
     *  digitosIniciales posicion 3  x 2
     *  digitosIniciales posicion 4  x 1
     *  digitosIniciales posicion 5  x 2
     *  digitosIniciales posicion 6  x 1
     *  digitosIniciales posicion 7  x 2
     *  digitosIniciales posicion 8  x 1
     *  digitosIniciales posicion 9  x 2
     *
     * Paso 2: Sí alguno de los resultados de cada multiplicación es mayor a o igual a 10, 
     * se suma entre ambos dígitos de dicho resultado. Ex. 12->1+2->3
     *
     * Paso 3: Se suman los resultados y se obtiene total
     *
     * Paso 4: Divido total para 10, se guarda residuo. Se resta 10 menos el residuo.
     * El valor obtenido debe concordar con el digitoVerificador
     *
     * Nota: Cuando el residuo es cero(0) el dígito verificador debe ser 0.
     * 
     * @param  string $digitosIniciales   Nueve primeros dígitos de CI/RUC
     * @param  string $digitoVerificador  Décimo dígito de CI/RUC
     * 
     * @return boolean
     *
     * @throws exception Cuando los digitosIniciales no concuerdan contra
     * el código verificador.
     */
    protected function algoritmoModulo10($digitosIniciales, $digitoVerificador)
    {
        $arrayCoeficientes = array(2,1,2,1,2,1,2,1,2);

        $digitoVerificador = (int)$digitoVerificador;
        $digitosIniciales = str_split($digitosIniciales);

        $total = 0;
        foreach ($digitosIniciales as $key => $value) {

            $valorPosicion = ( (int)$value * $arrayCoeficientes[$key] );

            if ($valorPosicion >= 10) {
                $valorPosicion = str_split($valorPosicion);
                $valorPosicion = array_sum($valorPosicion);
                $valorPosicion = (int)$valorPosicion;
            }

            $total = $total + $valorPosicion;
        }

        $residuo =  $total % 10;

        if ($residuo == 0) {
            $resultado = 0;        
        } else {
            $resultado = 10 - $residuo;
        }

        if ($resultado != $digitoVerificador) {
            throw new Exception('Dígitos iniciales no validan contra Dígito Idenficador');
        }

        return true;
    }

    /**
     * Get error
     *
     * @return string Mensaje de error
     */
    public function getError()
    {
        return $this->error;
    }
    
    /**
     * Set error
     *
     * @param  string $newEerror
     * @return object $this
     */
    public function setError($newError)
    {
        $this->error = $newError;
        return $this;
    }
}
?>