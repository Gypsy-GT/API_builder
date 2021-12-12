<?php 
class Common{

    private function __constructor(){
        
    }

    private static function getDefault( $args, $default, $passedArgs ){
        //initilize default data
        foreach($args as $value){
            if(!array_key_exists($value, $passedArgs)){
                if(!array_key_exists($value, $default)){
                    echo $value;
                    //if must have arguments is not present (must have = not in default but in args)
                    return false;
                }
                $passedArgs[$value] = $default[$value];
            }
        }
        return $passedArgs;
    }

    public static function renderFunction( $args ){
        $arguments = ['functionType', 'functionName', 'lines', 'tabs', 'text'];
        $defaultValues = [
            "functionType" => "",
            "functionName" => "",
            "tabs" => 1,
            "lines" => 1,
            "text" => "",
            "params" => ""
        ];
        $args = self::getDefault($arguments, $defaultValues, $args);
        if(!$args){
            echo "Invalid data passed";
            return;
        }

        $toReturn = "function " . $args['functionType'] . " " .$args['functionName']." ( ". $args['params'] ." ) {\n";
        $newLine = str_repeat("\n", $args['lines']);
        $tabs = str_repeat("\t", $args['tabs']);
        $toRender = explode("\n", $args['text']);

        foreach( $toRender as $data ){
            $toReturn .= "$tabs$data$newLine";
        }
        $toReturn .= "\t}";
        return $toReturn;
    }
    /**
     * Function to validate and add if timestamp key is set to true in the input.json file.
     */

    public static function processEnv( $jsonInput ){
       
        $path = __DIR__ . "/../";
        $data = file_get_contents( $path .".env", TRUE );

        $data = $data . "PROJECT_PATH=/../../../../release";
        $data = $data . "\nAPP_NAME=" . $jsonInput->config->app_name;
        $data = $data . "\nDB_CONNECTION=mysql\nDB_HOST=127.0.0.1\nDB_PORT=3306\n";
        $data = $data . "DB_DATABASE=" . $jsonInput->config->database_name;
        $data = $data . "\nDB_USERNAME=" . $jsonInput->config->database_username;
        $data = $data . "\nDB_PASSWORD=" . $jsonInput->config->database_password;

        file_put_contents( $path .".env", $data);
       
        preg_match_all( '/\w+=([\w\S]+){0,}/s' , $data, $matches);
        $envData = Common::makeAssoc($matches[0]);
        
        foreach (Constant::PROJECT_ENV  as $value ) {
            if(array_key_exists($value, $envData)){
                define( trim( $value ), trim( $envData[$value] ) );
            }
        }
    }

    public static function makeAssoc( $data ){
        $toReturn = [];
        foreach($data as $value){
            $toPut = explode("=", $value);
            $toReturn[$toPut[0]] = $toPut[1];
        }
        return $toReturn;
    }

    public static function override_env() {
        $path = __DIR__ . "/../";
        $gotData = file_get_contents( $path . ".env" );
        $toOverride = file_get_contents( __DIR__ . PROJECT_PATH . "/.env");
       
        preg_match_all( '/([\w\S ]+)=([\w\S ]+){0,}/s' , $gotData, $rawGotData);
        preg_match_all( '/([\w\S ]+)=([\w\S ]+){0,}/s' , $toOverride, $rawToOverride);
        
        $rawGotData = self::makeAssoc($rawGotData[0]);
        $rawToOverride = self::makeAssoc($rawToOverride[0]);

        $finalData = array_merge($rawToOverride, $rawGotData);
     
        $toWrite = ""; $count = 0;
        foreach($finalData as $key => $value){
            if(!in_array($key, Constant::PROJECT_ENV)){
                $toWrite .= trim($key) . "=" . trim($value) . "\n";
                $count++;
                if ($count % 4 == 0 ) $toWrite .= "\n\n";
            }
        }
       
        file_put_contents( __DIR__ . PROJECT_PATH . "/.env", $toWrite);
      
    }

    public static function validate_timestamp( $json )
    {
        if( is_array( $json ) )
        {
            foreach( $json as &$value )
            {
            
                   
                    switch ( $value->model->timestamps ) {
                        case TRUE:                         
                            $model_prop = $value->model;
                          
                            if( $model_prop->timestamps )
                            {
                                $value->model->fields->created_at = "date";
                                $value->model->fields->updated_at = "datetime";

                                $value->model->fillable[] = "created_at";
                                $value->model->fillable[] = "updated_at";
                            }
                            break;
                        default:
                            break;
                    }
               
                    
            }   
        }
    
        return $json;
    } 
    /**
     * Function to Generate / write the dynamic script in server.sh file.
     */
    public static function get_basic_script_commands( $project_path )
    {
        if( isset( $project_path ) )
        {
            $server_port = SERVER_PORT;
            $common_command = "cd " . $project_path . " && ";
            
            if( $_SESSION['global_counter'] == 0 )
            {
                if( isset( $server_port ) )
                {
                    $common_command .= "nohup " . Constant::COMMANDS['START_SERVER']["WITH_PORT"] . " & > /dev/null 2>&1"  ;
        
                }
                else
                {
                    $common_command .= "nohup " . Constant::COMMANDS['START_SERVER']["WITHOUT_PORT"] . " & > /dev/null 2>&1";
                }

                
                $_SESSION['global_counter'] += 1 ; 
            }
            shell_exec( $common_command );
        }
              
    }

    /**
     * Function to clear the .env file after it's work is done
     */
    public static function clear_initial_env() {
        $path = __DIR__ . "/../";
        $data = "";
        file_put_contents( $path .".env", $data);
        return true;
    }
    
}
?>