<script>
    $(document).ready(function() {
        $(".editPriceBook").click(function() {
            var priceBookID = $(this).data("id");
            var ajaxurl = "pricebookEdit/"+priceBookID;

            $.ajax({
                url: ajaxurl,
                type: "GET",
                success: function(result) {
                    $("#editPriceBook").find('input[name="id"]').val(result.data.id);
                    $("#editPriceBook").find('input[name="name"]').val(result.data.name);
                    $("#editPriceBook").find('input[name="external_id"]').val(result.data.external_id);
                    if (result.data.fixed_price == 1) {
                        $("#editPriceBook").find('input[name="fixed_price"]').prop('checked', true);
                    }
                    if (result.data.provider_travel == 1) {
                        $("#editPriceBook").find('input[name="provider_travel"]').prop('checked', true);
                    }

                    var myModal = new bootstrap.Modal(document.getElementById('editPriceBook'))
                    myModal.show();

                }
            });
        });


        $(".addPrice").click(function() {
            $("#addPrice").find('input[name="price_book_id"]').val($(this).data("id"));

            var myModal = new bootstrap.Modal(document.getElementById('addPrice'))
            myModal.show();
        });


        $(".editPrice").click(function() {

            var priceID = $(this).data("id");
            var ajaxurl = "prices/"+priceID+"/edit";

            $.ajax({
                url: ajaxurl,
                type: "GET",
                success: function(result) {
                    $("#editPrice").attr('action', '{{route("prices.update",['+priceID+'])}}');
                    $("#editPrice #day_of_week").val(result.data.day_of_week);
                    $("#editPrice").find('input[name="start_time"]').val(result.data.start_time);
                    $("#editPrice").find('input[name="end_time"]').val(result.data.end_time);
                    $("#editPrice").find('input[name="per_hour"]').val(result.data.per_hour);
                    $("#editPrice").find('input[name="refrence_no_hr"]').val(result.data.refrence_no_hr);
                    $("#editPrice").find('input[name="per_km"]').val(result.data.per_km);
                    $("#editPrice").find('input[name="refrence_no"]').val(result.data.refrence_no);
                    $("#editPrice").find('input[name="effective_date"]').val(result.data.effective_date);
                    $("#editPrice #multiplier").val(result.data.multiplier);

                    var myModal = new bootstrap.Modal(document.getElementById('editPrice'))
                    myModal.show();

                }
            });
            
        });
    });
</script>