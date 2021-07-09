<?php
include __DIR__."/Classes/Model.php";
include __DIR__."/Classes/Controller.php";
include __DIR__."/Classes/Route.php";
include __DIR__."/Classes/Migration.php";

class Main{

    public function __construct( $jsonFile ) { 
        
        $this->jsonInput = json_decode( file_get_contents( $jsonFile ) );
        $this->processEnv();
        $project_path = PROJECT_PATH ;
        if( ! is_dir( $project_path ) ) {
            echo shell_exec( "composer create-project --prefer-dist laravel/laravel " . $project_path ) ;
        }

        $this->model = new Model( $this->jsonInput );
        $this->route = new Route( $this->jsonInput );
        $this->controller = new Controller( $this->jsonInput );

        $this->migration = new Migration( $this->jsonInput );

        $this->testing();
    }
    
    public function processEnv(){
        $data = file_get_contents( ".env" );

        preg_match_all( '/(.*)=(.*)/s' , $data, $matches);
        for ( $i = 0 ; $i < count( $matches[1] ) ; $i++ ) {
            define( trim( $matches[1][$i] ), trim( $matches[2][$i] ) );
        }
    }

    public function testing(){
        // $this->model->processFile();
    }
    
}
$main = new Main("./input.json");
?>