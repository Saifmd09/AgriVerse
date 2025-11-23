document.getElementById("verifyForm").addEventListener("submit", function(e){
    e.preventDefault();

    let fd = new FormData();
    fd.append("citizen_type", document.getElementById("citizen_type").value);
    fd.append("citizen_proof", document.getElementById("citizen_proof").files[0]);
    fd.append("bank_proof", document.getElementById("bank_proof").files[0]);

    fetch("../backend/submit-investor-verification.php", {
        method: "POST",
        body: fd
    })
    .then(res => res.text())
    .then(data => {
        const msg = document.getElementById("msg");

        if (data === "SUCCESS") {
            msg.style.color = "green";
            msg.innerHTML = "✅ Verification Submitted!";
            setTimeout(() => {
                window.location.href = "under_verification.html";
            }, 1500);
        } else {
            msg.style.color = "red";
            msg.innerHTML = data;
        }
    });
});
