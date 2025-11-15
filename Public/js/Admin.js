//TODO, ve všech async/await kontrolovat chyby, prohlížeč je automaticky nedetekuje a chyby nevyhazuje

// ---------------------------------------------------------
// 1) Načtení všech uživatelů
// ---------------------------------------------------------
async function loadUsers() {
    //TODO, GET by byl vhodnější (praxe), ale kvůli tomu jak funguje router, je to jednodušší dělat přes POST
    //potřeba dva operátory await, je ten, že operace fetchování dat probíhá ve dvou odlišných fázích
    //jeden čeká na HTTP odpověď, druhý čeká na dekódování JSON těla
    //  vrací Promise, který se vyřeší, až přijde odpověď ze sítě
    const res = await fetch("/admin/apiGetUsers", { method: "POST" });
    //  metoda objektu Response také vrací Promise, protože tělo odpovědi může být načítáno/streamováno a parsování JSON může chvíli trvat
    const users = await res.json();

    const tbody = document.querySelector("#usersTable tbody");
    tbody.innerHTML = "";

    users.forEach(u => {
        const tr = document.createElement("tr");

        tr.innerHTML = `
            <td>${u.id}</td>
            <td>${u.nickname}</td>
            <td>${u.first_name}</td>
            <td>${u.last_name}</td>
            <td>${u.email}</td>
            <td>
                <select class="role-select" data-id="${u.id}">
                    <option value="author" ${u.role === 10 ? "selected" : ""}>author</option>
                    <option value="reviewer" ${u.role === 20 ? "selected" : ""}>reviewer</option>
                    <option value="admin" ${u.role === 100 ? "selected" : ""}>admin</option>
                    <option value="superadmin" ${u.role === 200 ? "selected" : ""}>superadmin</option>
                </select>
            </td>
            <td>
                <input type="checkbox" class="active-toggle" data-id="${u.id}" ${u.is_active === 1 ? "checked" : ""}>
            </td>
            <td>
                <button class="btn btn-sm btn-primary save-btn" data-id="${u.id}">Uložit</button>
            </td>
        `;

        tbody.appendChild(tr);
    });

    attachHandlers();
}

// ---------------------------------------------------------
// 2) Event listenery po vytvoření tabulky
// ---------------------------------------------------------
function attachHandlers() {
    document.querySelectorAll(".save-btn").forEach(btn => {
        btn.addEventListener("click", async (e) => {

            const id = e.target.dataset.id;
            const tr = e.target.closest("tr");

            // Získání obou hodnot
            const role = tr.querySelector(".role-select").value;
            // Důležité: 'is_active' je boolean (true/false)
            const is_active = tr.querySelector(".active-toggle").checked;

            if (!confirm("Opravdu uložit změny?")) return;

            const response = await fetch("/admin/apiUpdateUser", { // <-- Používáme nový jednotný endpoint
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    user_id: id,
                    role: role,
                    is_active: is_active
                })
            });

            const jsonResponse = await response.json();

            //TODO, lépe si poradit s errory
            if (jsonResponse.error) {
                console.error("Chyba při aktualizaci uživatele:", jsonResponse.error);
                alert(jsonResponse.error);
                loadUsers(); // Znovu načíst data, pokud došlo k chybě
                return;
            }

            //TODO, lépe si poradit s: Kontrola úspěchu (volitelné, ale dobrá praxe)
            if (!jsonResponse.success) {
                console.error("Aktualizace selhala, ale bez specifické chybové zprávy.");
                alert("Aktualizace se nezdařila.");
                loadUsers();
                return;
            }

            // Krátké zvýraznění změny (pokud úspěšně proběhla)
            tr.classList.add("highlight");
            setTimeout(() => tr.classList.remove("highlight"), 1500);
        });
    });
}

// ---------------------------------------------------------
// 3) Řazení tabulky
// ---------------------------------------------------------

//vybírá jen ty elementy <th>, které mají atribut data-sort
document.querySelectorAll("th[data-sort]").forEach(th => {
    th.addEventListener("click", () => {
        const key = th.dataset.sort;
        sortTable(key);
    });
});

function sortTable(key) {
    const tbody = document.querySelector("#usersTable tbody");
    const rows = Array.from(tbody.querySelectorAll("tr"));

    const indexMap = {
        id: 0,
        nickname: 1,
        first_name: 2,
        last_name: 3,
        email: 4,
        role: 5,
        is_active: 6
    };

    const idx = indexMap[key];

    rows.sort((a, b) => {
        const A = a.children[idx].innerText.toLowerCase();
        const B = b.children[idx].innerText.toLowerCase();
        return A.localeCompare(B, "cs");
    });

    tbody.innerHTML = "";
    rows.forEach(r => tbody.appendChild(r));
}

// ---------------------------------------------------------
// Inicializace
// ---------------------------------------------------------
loadUsers();
