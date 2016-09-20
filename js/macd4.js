           /* COLUMN 1 , ROW 1 */
            $("body").on("click", "#set11", function (e) {
                $("#fastPeriod").val("23");
                $("#slowPeriod").val("14");
                $("#signalPeriod").val("9");
                $("#BTC").val("1");
                $("#xsteps").val("1");
                $("#minpctup").val("1.25");
                $("#maxpctdn").val("6");
                $("#method").val("4");
                $('#ds2').prop('checked', true);
                $('#BTC_XRP').prop('selected', true);
            });
            $("body").on("hover", "#set11", function (e) {
                $("#show").html("-f23 -s14 -S9 -pBTC_XRP -c1 -x1 -U1.25 -D6 -X4 -mt -z2");
            });
            /* COLUMN 1 , ROW 2 */
            $("body").on("click", "#set12", function (e) {
                $("#fastPeriod").val("23");
                $("#slowPeriod").val("14");
                $("#signalPeriod").val("9");
                $("#BTC").val("1");
                $("#xsteps").val("1");
                $("#minpctup").val("1.25");
                $("#maxpctdn").val("6");
                $("#method").val("4");
                $('#ds5').prop('checked', true);
                $('#C3_hidden_triggers').prop('selected', true);
            });
            $("body").on("hover", "#set12", function (e) {
                $("#show").html("-f23 -s14 -S9 -p'C3_hidden_triggers' -mt -c1 -x1 -U1.25 -D6 -z5");
            });
            /* COLUMN 1 , ROW 3 */

            $("body").on("click", "#set13", function (e) {
                $("#fastPeriod").val("23");
                $("#slowPeriod").val("14");
                $("#signalPeriod").val("9");
                $("#BTC").val("1");
                $("#xsteps").val("1");
                $("#minpctup").val("1.25");
                $("#maxpctdn").val("6");
                $("#method").val("4");
                $('#ds6').prop('checked', true);
                $('#temp').prop('selected', true);
            });
            $("body").on("hover", "#set13", function (e) {
                $("#show").html("-f23 -s14 -S9 -ptemp -c1 -x1 -U1.25 -D6 -X4 -mt -z6");
            });

            /* COLUMN 2 , ROW 1 */

            $("body").on("click", "#set21", function (e) {
                $("#fastPeriod").val("12");
                $("#slowPeriod").val("5");
                $("#signalPeriod").val("3");
                $("#BTC").val("1");
                $("#xsteps").val("1");
                $("#minpctup").val("1.25");
                $("#maxpctdn").val("6");
                $("#method").val("4");
                $('#ds2').prop('checked', true);
                $('#BTC_XRP').prop('selected', true);
            });
            $("body").on("hover", "#set21", function (e) {
                $("#show").html("-f12 -s5 -S3 -pBTC_XRP -c1 -x1 -U1.25 -D6 -X4 -mt -z2");
            });
            /* COLUMN 2 , ROW 2 */
            $("body").on("click", "#set22", function (e) {
                $("#fastPeriod").val("23");
                $("#slowPeriod").val("14");
                $("#signalPeriod").val("9");
                $("#BTC").val("1");
                $("#xsteps").val("1");
                $("#minpctup").val("1.25");
                $("#maxpctdn").val("6");
                $("#method").val("4");
                $('#ds5').prop('checked', true);
                $('#C2_obvious_triggers').prop('selected', true);
            });
            $("body").on("hover", "#set22", function (e) {
                $("#show").html("-f23 -s14 -S9 -p'C3_obvious_triggers' -mt -c1 -x1 -U1.25 -D6 -z5");
            });
            /* COLUMN 2 , ROW 3 */
            $("body").on("click", "#set23", function (e) {
                $("#fastPeriod").val("23");
                $("#slowPeriod").val("14");
                $("#signalPeriod").val("9");
                $("#BTC").val("1");
                $("#xsteps").val("1");
                $("#minpctup").val("1.25");
                $("#maxpctdn").val("6");
                $("#method").val("4");
                $('#ds4').prop('checked', true);
                $('#ADP').prop('selected', true);
            });
            $("body").on("hover", "#set23", function (e) {
                $("#show").html("-f23 -s14 -S9 -pADP -c1 -x1 -U1.25 -D6 -X4 -mt -z4  (web only)");
            });
            /* cole 3 */
            //            $("body").on("click", "#set31", function (e) {
            //                $("#fastPeriod").val("23");
            //                $("#slowPeriod").val("14");
            //                $("#signalPeriod").val("9");
            //                $("#BTC").val("1");
            //                $("#xsteps").val("1");
            //                $("#minpctup").val("1.25");
            //                $("#maxpctdn").val("6");
            //                $("#method").val("4");
            //                $('#ds2').prop('checked',true);
            //                $('#BTC_LTC').prop('selected',true);
            //            });
            //            $("body").on("hover", "#set31", function (e) {
            //                $("#show").html("-f23 -s14 -S9 -pBTC_LTC -c1 -x1 -U1.25 -D6 -X4 -mt -z2");
            //            });
            //            $("body").on("click", "#set32", function (e) {
            //                $("#fastPeriod").val("12");
            //                $("#slowPeriod").val("7");
            //                $("#signalPeriod").val("4");
            //                $("#BTC").val(".1");
            //                $("#xsteps").val("2");
            //                $("#minpctup").val("1.25");
            //                $("#maxpctdn").val("6");
            //                $("#method").val("3");
            //                $('#ds2').prop('checked',true);
            //                $('#BTC_LTC').prop('selected',true);
            //            });
            //            $("body").on("hover", "#set32", function (e) {
            //                $("#show").html("-f12 -s7 -S4 -pBTC_LTC -c1 -x2 -U1.25 -D6 -X3 -mt -z2");
            //            });
            //            $("body").on("click", "#set33", function (e) {
            //                $("#fastPeriod").val("12");
            //                $("#slowPeriod").val("7");
            //                $("#signalPeriod").val("4");
            //                $("#BTC").val("1");
            //                $("#xsteps").val("1");
            //                $("#minpctup").val("1.25");
            //                $("#maxpctdn").val("6");
            //                $("#method").val("4");
            //                $('#ds2').prop('checked',true);
            //                $('#BTC_LTC').prop('selected',true);
            //            });
            //            $("body").on("hover", "#set33", function (e) {
            //                $("#show").html("-f12 -s7 -S4 -pBTC_LTC -c1 -x1 -U1.25 -D6 -X4 -mt -z2");
            //            });

