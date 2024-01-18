    function updateSozlukBasligi(yazilanMetin) {
        var sozlukBasligi = document.getElementById("sozlukBasligi");
        if (yazilanMetin.trim() === "") {
            sozlukBasligi.innerText = "Sözlük.";
        } else {
            sozlukBasligi.innerText = yazilanMetin;
        }
    }

    function showRegistrationModal() {
        document.getElementById("registrationModal").classList.add("active");
    }

    function hideRegistrationModal() {
        document.getElementById("registrationModal").classList.remove("active");
    }

    function showLoginModal() {
        document.getElementById("loginModal").classList.add("active");
    }

    function hideLoginModal() {
        document.getElementById("loginModal").classList.remove("active");
    }

    function showAddWordModal() {
        document.getElementById("addWordModal").style.display = 'flex';
    }

    function hideAddWordModal() {
        document.getElementById("addWordModal").style.display = 'none';
    }
    document.getElementById("aramaInput").addEventListener("keyup", function(event) {
        if (event.key === "Enter") {
            document.getElementById("aramaForm").submit();
        }
    });

