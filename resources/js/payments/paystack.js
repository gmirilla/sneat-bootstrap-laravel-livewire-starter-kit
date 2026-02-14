window.PaystackPayment = {
    start(policyId) {

        fetch(`/payments/paystack/init/${policyId}`)
            .then(res => res.json())
            .then(data => {

                const handler = PaystackPop.setup({
                    key: data.public_key,
                    email: data.email,
                    reference: data.reference,

                    callback: function(response){
                        document.getElementById('paystack_reference').value = response.reference;
                        document.forms[0].submit();
                    }
                });

                handler.openIframe();
            });
    }
};
