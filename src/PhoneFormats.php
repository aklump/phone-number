<?php
// SPDX-License-Identifier: BSD-3-Clause

namespace AKlump\PhoneNumber;

class PhoneFormats {

  /**
   * @link https://www.itu.int/rec/T-REC-E.164/en
   */
  const E164 = '+#CC# #c# ### ####';

  /**
   * @link https://nationalnanpa.com/
   */
  const NANP = '(#c#) ###-####';

  const SMS = '+#CC##c########';

  const JSON = '{"country":"+#CC#","areaCode":#c#,"localExchange":###,"subscriberNumber":####}';
}
