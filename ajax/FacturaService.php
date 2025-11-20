<?php

// "https://demoemision.thefactoryhka.com.pa/ws/obj/v1.0/Service.svc?singleWsdl"
class SoapHandler
{
  private $client;

  public function __construct(string $url, array $options = [])
  {
    try {
        $defaultOptions = [
          'trace' => 1,
          'exceptions' => true,
          'cache_wsdl' => 0
        ];
        $this->client = new SoapClient($url, array_merge($defaultOptions, $options));
    } catch (SoapFault $e) {
        throw new Exception("Error al conectar con el servicio SOAP: " . $e->getMessage());
    }
  }

  public function call(string $method, array $params = [])
  {
    try {
        return $this->client->__soapCall($method, [$params]);
    } catch (SoapFault $e) {
        // Retornar un objeto con estructura de error consistente
        return (object)[
          'codigo' => 500,
          'mensaje' => 'Error SOAP: ' . $e->getMessage(),
          'err' => $e->getMessage()
        ];
    }
  }

  public function getFunctions(): array
  {
    return $this->client->__getFunctions();
  }

  public function getTypes(): array
  {
    return $this->client->__getTypes();
  }

  public function getLastRequest(): string
  {
    return $this->client->__getLastRequest();
  }

  public function getLastResponse(): string
  {
    return $this->client->__getLastResponse();
  }
}

class FacturaService
{
   private string $tokenId;
   private string $tokenPassword;
   private string $url;
   private string $empresa = "GIRAG";
   private SoapHandler $soap;
   private array $params;

   public function __construct($tokenId, $tokenPassword, $url)
   {
      try {
         $this->soap = new SoapHandler($url);
         $this->tokenId = $tokenId;
         $this->tokenPassword = $tokenPassword;

         $this->params = [
            "tokenEmpresa" => $tokenId,
            "tokenPassword" => $tokenPassword
         ];
      } catch (Exception $e) {
         // Log del error
         error_log("Error inicializando FacturaService: " . $e->getMessage());
         throw $e;
      }
   }

   public function verificarRuc(string $ruc, int $tipoContribuyente)
   {
      try {
         $request = [
            "consultarRucDVRequest" => [
               "tokenEmpresa"   => $this->tokenId,
               "tokenPassword"  => $this->tokenPassword,
               "tipoRuc"        => $tipoContribuyente,
               "ruc"            => $ruc
            ]
         ];

         $response = $this->soap->call("ConsultarRucDv", $request);
         
         // Si la respuesta tiene código de error, retornarla tal cual
         if (isset($response->codigo) && $response->codigo == 500) {
            return $response;
         }
         
         return $response->ConsultarRucDVResult;
      } catch (Exception $e) {
         error_log("Error en verificarRuc: " . $e->getMessage());
         return (object)[
            'codigo' => 500,
            'mensaje' => 'Error al verificar RUC: ' . $e->getMessage()
         ];
      }
   }

   public function enviarFactura($factura)
   {
      try {
         $request = [
            "tokenEmpresa"   => $this->tokenId,
            "tokenPassword"  => $this->tokenPassword,
            "documento"      => $factura,
         ];

         $response = $this->soap->call("Enviar", $request);
         
         // Si la respuesta tiene código de error del SOAP handler
         if (isset($response->codigo) && $response->codigo == 500) {
            error_log("Error SOAP al enviar factura: " . $response->mensaje);
            return $response;
         }
         
         // Si la respuesta es exitosa, retornar el resultado
         if (isset($response->EnviarResult)) {
            return $response->EnviarResult;
         }
         
         // Si llegamos aquí, algo inesperado pasó
         return (object)[
            'codigo' => 500,
            'mensaje' => 'Respuesta inesperada del servicio de facturación'
         ];
         
      } catch (Exception $e) {
         error_log("Excepción en enviarFactura: " . $e->getMessage());
         return (object)[
            'codigo' => 500,
            'mensaje' => 'Error de conexión: ' . $e->getMessage(),
            'err' => $e->getMessage()
         ];
      }
   }
}

$hkApi = new FacturaService(
   "aojjjucbweqb_tfhka", 
   "CmY.ZAMYYV+!", 
   "https://demoemision.thefactoryhka.com.pa/ws/obj/v1.0/Service.svc?singleWsdl"
);
?>