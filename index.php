<?php
$funcion_de_adaptacion=function($individuo) {
		return $individuo;
};

$algoritmo_genetico=new Algoritmo_genetico();
$poblacion_final=$algoritmo_genetico->ejecutar($funcion_de_adaptacion);
exit();

class Algoritmo_genetico
{
	const GENES_POR_INDIVIDUO=8;
	const POBLACION_INICIAL=500;
	const CANTIDAD_DE_INDIVIDUOS_SELECCIONADOS=20; 
	const CANTIDAD_DE_CRUCES_POR_INDIVIDUO=25; # Mantener la poblacion inicial constante!
	const PROBABILIDAD_DE_MUTACION_DE_GEN=0.5; # Porcentaje # Mutacion simple, pero hay otros tipos de mutacion
	const CANTIDAD_DE_VUELTAS_MAXIMAS=100; # Hay muchos criterios de detencion
	public function ejecutar($funcion_de_adaptacion){
		$poblacion=$this->generar_poblacion_inicial();
		echo 'Promedio inicial de la población: '.array_sum($poblacion)/count($poblacion).PHP_EOL;
		$detencion=false;
		for ($i=0; $i < self::CANTIDAD_DE_VUELTAS_MAXIMAS AND !$detencion; $i++) {
			$poblacion=$this->mutacion($this->cruzamiento($this->seleccion($poblacion,$funcion_de_adaptacion)));
			if($i % (self::CANTIDAD_DE_VUELTAS_MAXIMAS/100)==0) {
				echo 'Promedio actual: '.array_sum($poblacion)/count($poblacion).PHP_EOL;
			}
		}
		echo 'Promedio final de la población: '.array_sum($poblacion)/count($poblacion).PHP_EOL;
		return $poblacion;
	}
	
	// Poblacion inicial
	private function generar_poblacion_inicial(){
		$poblacion=array();
		for ($i=0; $i < self::POBLACION_INICIAL; $i++) { 
			$poblacion[]=$this->generar_individuo();
		}
		return $poblacion;
	}
	private function generar_individuo(){
		return rand(0,pow(2, self::GENES_POR_INDIVIDUO));
	}
	private function seleccion($poblacion,$funcion_de_adaptacion){
		return $this->seleccion_ruleta($poblacion,$funcion_de_adaptacion);
	}
	// Seleccion
	private function seleccion_torneo($poblacion){
	}
	private function seleccion_ranking($poblacion,$funcion_de_adaptacion)	{
		$ranking=array();
		foreach ($poblacion as $individuo) {
			$ranking[$individuo]=$funcion_de_adaptacion($individuo);
		}
		arsort($ranking);
		return array_slice(array_keys($ranking),0, self::CANTIDAD_DE_INDIVIDUOS_SELECCIONADOS);
	}
	private function seleccion_ruleta($poblacion,$funcion_de_adaptacion){
		$total=0;
		foreach ($poblacion as $individuo) {
			$total+=$funcion_de_adaptacion($individuo);
		}
		$ruleta=array();
		$acumulado=0;
		foreach ($poblacion as $individuo) {
			$adaptacion= $funcion_de_adaptacion($individuo)/$total;
			$acumulado+=$adaptacion;
			$ruleta[]=array($individuo,$adaptacion, $acumulado);
			// error_log($individuo.' : '.$adaptacion.' : '.$acumulado);
		}
		$poblacion_seleccionada=array();
		for ($i=0; $i < self::CANTIDAD_DE_INDIVIDUOS_SELECCIONADOS; $i++) {
			$rand=rand(0,10000000000)/10000000000;
			$individuo_seleccionado=false;
			foreach ($ruleta as $key => $array) {
				if(!$individuo_seleccionado AND $rand<=$array[2]){
					$individuo_seleccionado=$array[0];
				}
			}
			$poblacion_seleccionada[]=$individuo_seleccionado;
		}
		return $poblacion_seleccionada;
	}
	private function seleccion_control_sobre_numero_esperado($poblacion){
	}
	private function cruzamiento($poblacion){
		$poblacion_cruzada=array();
		foreach ($poblacion as $key => $individuo) {
			for ($i=0; $i < self::CANTIDAD_DE_CRUCES_POR_INDIVIDUO; $i++) { 
				$individuo_al_azar=$poblacion[rand(0,count($poblacion)-1)];
				$poblacion_cruzada[]=$this->cruzar_individuos_simple($individuo,$individuo_al_azar);
			}
		}
		return $poblacion_cruzada;
	}
	// Cruzamiento
	private function cruzar_individuos_simple($x,$y){
		$punto_de_corte=rand(0,self::GENES_POR_INDIVIDUO-1);
		$x_binario=decbin($x);
		$y_binario=decbin($y);
		$hijo_binario=substr($x_binario,0,$punto_de_corte).substr($y_binario, $punto_de_corte);
		$hijo=bindec($hijo_binario);
		return $hijo;
	}
	private function cruzar_individuos_multipunto($x,$y){
	}
	private function cruzar_individuos_binomial($x,$y){
	}
	private function cruzar_individuos_azar($x,$y){
	}
	// Mutacion
	private function mutacion($poblacion){
		$poblacion_mutada=array();
		foreach ($poblacion as $individuo) {
			$poblacion_mutada[]=$this->mutar_individuo($individuo);
		}
		return $poblacion_mutada;
	}
	private function mutar_individuo($individuo){
		$individuo_binario=str_pad(decbin($individuo),self::GENES_POR_INDIVIDUO,'0',STR_PAD_LEFT) ;
		$individuo_mutado_binario='';
		$length=strlen($individuo_binario);
		for ($i=0; $i < self::GENES_POR_INDIVIDUO; $i++) { 
			$rand=rand(0,100);
			if($rand<=self::PROBABILIDAD_DE_MUTACION_DE_GEN){
				if($individuo_binario[$i]=='1'){
					$individuo_mutado_binario.='0';
				}
				else{
					$individuo_mutado_binario.='1';
				}
			}
			else{
				$individuo_mutado_binario.=$individuo_binario[$i];
			}
		}
		return bindec($individuo_mutado_binario);
	}
}
