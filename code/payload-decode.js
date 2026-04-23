// format
// BAT (2o) | temp1 (2o) | humidity (2o) | ext# (1o) | extvalue (4)|
// avec BAT == 2 premiers bits état de la batterie
//             14 derniers bits: voltage de la batterie en mV
// avec humidity: diviser par 10 pour avoir le pourcentage
// avec ext#, eb bits: | 0S00 EEEE |
//    bit S indique le status, 0 pour le mode normal
//    4 bits du bas (EEEE) valent 1 pour notre senseur externe
// temp1 ou la partie température de extvalue (2 premiers octets):
//    si > 0x8000, d'abord retirer 65536
//    ensuite, diviser par 100 pour avoir la température
// (sans senseur externe, 0x7FFF)
function Decoder(input) {
   let decode = {};
   let bytes;
   if (input && input.bytes) {
        bytes = input.bytes; // Standard actuel (TTN v3, ChirpStack v4)
    } else if (Array.isArray(input)) {
        bytes = input; // Anciennes versions (ChirpStack v3)
    } else {
        return { errors: ["Impossible de trouver les octets dans l'input"] };
    }
    
   let ext = bytes[6] & 0x0F;
   if (bytes[6] & 0x40) {
      decode.error = 'Decoder does not process this';
   }
   else {
      decode.battery_status = (bytes[0] & (0x80|0x40)) >> 6;
      decode.battery_voltage = ((bytes[0] << 8) | bytes[1]) & 0x3FFF;
      
      let temp1_raw = (bytes[2] << 8) | bytes[3];
      if (temp1_raw > 0x8000) {
         temp1_raw -= 65536;
      }
      decode.temp1 = temp1_raw / 100;
      
      decode.humidity = (((bytes[4] << 8) | bytes[5]) / 10);
      
      if (ext == 1) {
         let ext_temp = (bytes[7] << 8) | bytes[8];
         if (ext_temp !== 0x7FFF) {
            if (ext_temp > 0x8000) {
               ext_temp -= 65536;
            }
            decode.ext_temperature = ext_temp / 100;
         }
      }
      else {
         decode.error = 'Unsupported external sensor type: ' + ext;
      }
   }
   if (typeof decode.error == 'undefined') {
      return { data: decode };
   }
   return { errors: [ decode.error ] };
}