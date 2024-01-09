const paymentForm = document.getElementById('paymentForm');
paymentForm.addEventListener("submit", payWithPaystack, false);

function payWithPaystack(e) {
    e.preventDefault();

    const publicKey = "pk_test_1ae4247c45d98b04975da563aade136d4dd248c8";
    const userId = document.getElementById("userId").value;

  let handler = PaystackPop.setup({
    key: publicKey, // Replace with your public key
    email: document.getElementById("email-address").value,
    amount: document.getElementById("amount").value * 100, // covert to kobo 
    ref: `${userId}_${Math.floor((Math.random() * 1000000000) + 1)}`, // generates a pseudo-unique reference. Please replace with a reference you generated. Or remove the line entirely so our API will generate one for you
    // label: "Optional string that replaces customer email"
    onClose: function(){
      alert('Window closed.');
    },
      callback: function (response) {
        console.log('Redirecting');
        const url = `http://www.google.com?verifyPayment=${response.reference}`;
        window.location.replace(url);
    }
  });

  handler.openIframe();
}
