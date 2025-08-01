// Selectam elementele importante din DOM (Document Object Model)
const collectionDropdown = document.getElementById("collectionDropdown");
const actionDropdown = document.getElementById("actionDropdown");
const tableContainer = document.getElementById("tableContainer");
const reloadButton = document.getElementById("reloadButton");
const searchInput = document.getElementById("searchInput");
const insertFormContainer = document.getElementById("insertFormContainer");
const queryDropdown = document.getElementById("queryDropdown");
const queryContainer = document.querySelector('label[for="queryDropdown"]').parentElement;
const updateInstruction = document.getElementById("updateInstruction");

let selectedCollection = "";

// Se definesc campurile dinamice pentru fiecare colectie
const dynamicFieldsPerCollection = {
    alocare_pacienti: ["ID_PAC", "ID_MED"],
    medici: ["ID_MED", "NUME", "PRENUME", "SPECIALIZARE", "TEL", "EMAIL", "STRADĂ", "NUMĂR", "ORAȘ", "ADR_CAB"],
    pacienti: ["ID_PAC", "NUME", "PRENUME", "CNP", "DATA_NAST", "GEN", "TEL", "EMAIL", "ADR"],
    consultatii: ["ID_CON", "ID_PAC", "ID_MED", "DATA_CON"],
    boli: ["ID_BL", "NUME_BL", "DESCRIERE"],
    diagnostice: ["ID_CON", "ID_BL", "SIMPTOME"],
    medicamente: ["ID_MEDICAM", "NUME", "CANT", "UI", "TIP_ADMIN"],
    schema_tratament: ["ID_MEDICAM", "FRECV_ZI", "NR_ZILE"]
};

// Functie pentru normalizarea textelor (fara diacritice si lowercase)
function normalizeText(text) {
    return text
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "")
        .replace(/ț/g, 't').replace(/ş/g, 's').replace(/ș/g, 's')
        .replace(/ă/g, 'a').replace(/î/g, 'i').replace(/â/g, 'a')
        .replace(/Ţ/g, 't').replace(/Ş/g, 's').replace(/Ă/g, 'a')
        .replace(/Î/g, 'i').replace(/Â/g, 'a')
        .toLowerCase();
}

// Gestiune schimbarea interogarilor speciale
queryDropdown.addEventListener("change", function () {
    const selectedQuery = this.value;
    updateInstruction.style.display = "none";
    reloadButton.style.display = "none";

    if (selectedQuery === "pacientiMedic") {
        const idMed = prompt("Introdu ID-ul medicului (ex: DOC001):");
        if (idMed) {
            loadPacientiByMed(idMed);
        }
    } else if (selectedQuery === "pacientiGripaMedici") {
        loadPacientiGripaMedici();
    }
    else if (selectedQuery === "consultatiiMed") {
        const idMed = prompt("Introdu ID-ul medicului (ex: DOC001):");
        if (idMed) {
            loadConsultatiiMed(idMed);
        }
    }


    // Resetare selectare colecție
    collectionDropdown.value = "";
    actionDropdown.value = "";
    searchInput.style.display = "none";
    insertFormContainer.style.display = "none";
    reloadButton.style.display = "none";
    document.getElementById("actionControls").style.display = "none";
});


// Functie pentru incarcarea consultatiilor unui medic
function loadConsultatiiMed(idMed) {
    fetch("controllers/ConsultatiiController.php?consultatiiMed=" + encodeURIComponent(idMed))
        .then(response => response.text())
        .then(html => {
            tableContainer.innerHTML = html;
        })
        .catch(error => {
            tableContainer.innerHTML = `<p style='color:red;'>Eroare: ${error.message}</p>`;
        });
}

// Functie pentru incarcarea pacientilor cu gripa si medicii lor
function loadPacientiGripaMedici() {
    fetch("controllers/DiagnosticeController.php?pacientiGripaMedici=1")
        .then(response => response.text())
        .then(html => {
            tableContainer.innerHTML = html;
        })
        .catch(error => {
            tableContainer.innerHTML = `<p style='color:red;'>Eroare: ${error.message}</p>`;
        });
}

// Gestionare schimbarea colectiei selectate
collectionDropdown.addEventListener("change", function () {
    selectedCollection = this.value;
    tableContainer.innerHTML = "";
    insertFormContainer.innerHTML = "";
    updateInstruction.style.display = "none";
    reloadButton.style.display = "none";

    if (selectedCollection !== "") {
        document.getElementById("actionControls").style.display = "flex";
        actionDropdown.value = "";
        searchInput.style.display = "none";
        insertFormContainer.style.display = "none";
        queryContainer.style.display = "none";
        loadFullCollection(selectedCollection);
    } else {
        document.getElementById("actionControls").style.display = "none";
        searchInput.style.display = "none";
        insertFormContainer.style.display = "none";
        reloadButton.style.display = "none";
        queryContainer.style.display = "block";
    }
});

// Gestionare schimbarea tipului de actiune: cautare, adaugare, modificare
actionDropdown.addEventListener("change", function () {
    const action = this.value;

    searchInput.style.display = "none";
    insertFormContainer.style.display = "none";
    updateInstruction.style.display = "none";

    if (action !== "search") {
        reloadButton.style.display = "none";
    }

    switch (action) {
        case "search":
            searchInput.style.display = "inline-block";
            reloadButton.style.display = "inline-block";
            loadFullCollection(selectedCollection);
            break;
        case "add":
            if (dynamicFieldsPerCollection[selectedCollection]) {
                generateInsertForm(dynamicFieldsPerCollection[selectedCollection]); // asigura crearea formularului
                insertFormContainer.style.display = "block"; // il face vizibil
            }
            break;
        case "update":
            updateInstruction.style.display = (action === "update") ? "inline" : "none";
            loadFullCollection(selectedCollection);
            break;
        case "delete":
            fetch("controllers/" + selectedCollection.charAt(0).toUpperCase() + selectedCollection.slice(1) + "Controller.php")
                .then(response => response.text())
                .then(html => {
                    tableContainer.innerHTML = html;

                    // Adaugare eveniment de click pentru butoanele delete
                    document.querySelectorAll(".delete-button").forEach(button => {
                        button.addEventListener("click", () => {
                            const row = button.closest("tr");
                            const idPac = row.dataset.idPac;
                            const idMed = row.dataset.idMed;

                            fetch("controllers/" + selectedCollection.charAt(0).toUpperCase() + selectedCollection.slice(1) + "Controller.php", {
                                method: "DELETE",
                                headers: {
                                    "Content-Type": "application/x-www-form-urlencoded"
                                },
                                body: `ID_PAC=${encodeURIComponent(idPac)}&ID_MED=${encodeURIComponent(idMed)}`
                            })
                                .then(res => res.json())
                                .then(data => {
                                    if (data.deletedCount > 0) {
                                        row.remove();
                                    } else {
                                        alert("Eroare: Documentul nu a fost șters.");
                                    }
                                })
                                .catch(error => {
                                    alert("Eroare la ștergere: " + error.message);
                                });
                        });
                    });
                });
            break;

    }
});

// Buton pentru reincarcarea colectiei
reloadButton.addEventListener("click", function () {
    if (selectedCollection !== "") {
        loadFullCollection(selectedCollection);
        searchInput.value = "";
    }
});

// Cautare live in tabel
searchInput.addEventListener("input", function () {
    const rawTerm = this.value.trim();
    if (!rawTerm) {
        loadFullCollection(selectedCollection);
        return;
    }

    const controllerName = selectedCollection.charAt(0).toUpperCase() + selectedCollection.slice(1) + "Controller.php";

    fetch("controllers/" + controllerName + "?search=" + encodeURIComponent(rawTerm))
        .then(response => response.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const normalizedTerm = normalizeText(rawTerm);

            doc.querySelectorAll('td').forEach(td => {
                const originalText = td.textContent;
                const normalizedText = normalizeText(originalText);

                const matches = getAllMatchIndexes(normalizedText, normalizedTerm);
                if (matches.length === 0) return;

                let highlighted = "";
                let lastIndex = 0;

                matches.forEach(index => {
                    highlighted += originalText.slice(lastIndex, index);
                    highlighted += "<mark>" + originalText.slice(index, index + rawTerm.length) + "</mark>";
                    lastIndex = index + rawTerm.length;
                });

                highlighted += originalText.slice(lastIndex);
                td.innerHTML = highlighted;
            });

            tableContainer.innerHTML = doc.body.innerHTML;
        })
        .catch(error => {
            tableContainer.innerHTML = `<p style='color:red;'>Eroare: ${error.message}</p>`;
        });
});

// Functie pentru gasirea tuturor pozitiilor de potrivire
function getAllMatchIndexes(text, term) {
    const indexes = [];
    let index = 0;
    while ((index = text.indexOf(term, index)) !== -1) {
        indexes.push(index);
        index += term.length;
    }
    return indexes;
}

// Incarcare intreaga colectie
function loadFullCollection(collection) {
    const controllerName = collection.charAt(0).toUpperCase() + collection.slice(1) + "Controller.php";

    fetch("controllers/" + controllerName)
        .then(response => response.text())
        .then(html => {
            tableContainer.innerHTML = html;
            attachDeleteHandlers();

            if (actionDropdown.value === "update") {
                makeTableEditable();
            }
        })
        .catch(error => {
            tableContainer.innerHTML = "<p style='color:red;'>Eroare la încărcare: " + error.message + "</p>";
        });
}

// Functie ce face tabela editabila prin dublu click
function makeTableEditable() {
    document.querySelectorAll("#tableContainer td").forEach(cell => {
        cell.addEventListener("dblclick", function () {
            const originalText = this.textContent;
            const input = document.createElement("input");
            input.type = "text";
            input.value = originalText;
            input.style.width = "100%";

            this.textContent = "";
            this.appendChild(input);
            input.focus();

            const handleUpdate = () => {
                const newValue = input.value.trim();
                const row = cell.closest("tr");
                const field = cell.dataset.field;

                if (!field || newValue === originalText) {
                    cell.textContent = originalText;
                    return;
                }

                const payload = {
                    collection: selectedCollection,
                    field: field,
                    newValue: newValue
                };

                // identificatori specifici fiecarei colectii
                if (selectedCollection === "alocare_pacienti") {
                    payload.ID_PAC = row.dataset.idPac;
                    payload.ID_MED = row.dataset.idMed;
                }
                else if (selectedCollection === "boli") {
                    payload.ID_BL = row.dataset.idBl;
                }
                else if (selectedCollection === "consultatii") {
                    payload.ID_CON = row.dataset.idCon;
                }
                else if (selectedCollection === "diagnostice") {
                    payload.ID_CON = row.dataset.idCon;
                    payload.ID_BL = row.dataset.idBl;
                }
                else if (selectedCollection === "medicamente") {
                    payload.ID_MEDICAM = row.dataset.idMedicam;
                }
                else if (selectedCollection === "medici") {
                    payload.ID_MED = row.dataset.idMed;
                }
                else if (selectedCollection === "pacienti") {
                    payload.ID_PAC = row.dataset.idPac;
                }
                else if (selectedCollection === "schema_tratament") {
                    payload.ID_MEDICAM = row.dataset.idMedicam;
                }
                else {
                    console.warn("Modificarea nu e implementată pentru această colecție.");
                    return;
                }

                fetch("controllers/" + selectedCollection.charAt(0).toUpperCase() + selectedCollection.slice(1) + "Controller.php", {
                    method: "PUT",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify(payload)
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.modifiedCount > 0) {
                            cell.textContent = newValue;
                            cell.style.backgroundColor = "#d4edda";
                            setTimeout(() => {
                                cell.style.backgroundColor = "";
                            }, 1000);
                        } else {
                            cell.textContent = originalText;
                            alert("Modificarea nu a fost salvată.");
                        }
                    })
                    .catch(err => {
                        cell.textContent = originalText;
                        alert("Eroare la modificare: " + err.message);
                    });
            };

            input.addEventListener("blur", handleUpdate);

            input.addEventListener("keydown", (e) => {
                if (e.key === "Enter") {
                    e.preventDefault(); // împiedică trecerea pe alt rând
                    input.blur();       // declanșează blur => handleUpdate
                }
            });
        });
    });
}

// Functie pentru generarea dinamica a formularului de adaugare
function generateInsertForm(fields) {
    insertFormContainer.innerHTML = "";

    const form = document.createElement("form");
    form.id = "dynamicInsertForm";

    fields.forEach(field => {
        const input = document.createElement("input");
        input.type = "text";
        input.placeholder = field;
        input.name = field;
        input.required = true;
        form.appendChild(input);
    });

    const button = document.createElement("button");
    button.textContent = "➕ Adaugă";
    button.type = "submit";
    form.appendChild(button);

    const error = document.createElement("p");
    error.id = "insertError";
    error.style.color = "red";
    form.appendChild(error);

    insertFormContainer.appendChild(form);

    form.addEventListener("submit", function (e) {
        e.preventDefault();

        const data = {};
        fields.forEach(f => {
            data[f] = form[f].value.trim();
        });

        const controllerName = selectedCollection.charAt(0).toUpperCase() + selectedCollection.slice(1) + "Controller.php";

        fetch("controllers/" + controllerName, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(data)
        })
            .then(res => res.text())
            .then(msg => {
                form.reset();
                loadFullCollection(selectedCollection);
            })
            .catch(err => {
                error.textContent = "Eroare: " + err.message;
            });
    });
}

// Functie pentru atasarea handlerelor de delete la butoanele de stergere
function attachDeleteHandlers() {
    document.querySelectorAll(".delete-button").forEach(button => {
        button.addEventListener("click", () => {
            const row = button.closest("tr");

            let body = "";
            if (selectedCollection === "alocare_pacienti") {
                const idPac = row.dataset.idPac;
                const idMed = row.dataset.idMed;
                body = `ID_PAC=${encodeURIComponent(idPac)}&ID_MED=${encodeURIComponent(idMed)}`;
            } else if (selectedCollection === "boli") {
                const idBl = row.dataset.idBl;
                body = `ID_BL=${encodeURIComponent(idBl)}`;
            } else if (selectedCollection === "consultatii") {
                const idCon = row.dataset.idCon;
                body = `ID_CON=${encodeURIComponent(idCon)}`;
            }
            else if (selectedCollection === "diagnostice") {
                const idCon = row.dataset.idCon;
                const idBl = row.dataset.idBl;
                body = `ID_CON=${encodeURIComponent(idCon)}&ID_BL=${encodeURIComponent(idBl)}`;
            }
            else if (selectedCollection === "medicamente") {
                const idMedicam = row.dataset.idMedicam;
                body = `ID_MEDICAM=${encodeURIComponent(idMedicam)}`;
            }
            else if (selectedCollection === "medici") {
                const idMed = row.dataset.idMed;
                body = `ID_MED=${encodeURIComponent(idMed)}`;
            }
            else if (selectedCollection === "pacienti") {
                const idPac = row.dataset.idPac;
                body = `ID_PAC=${encodeURIComponent(idPac)}`;
            }
            else if (selectedCollection === "schema_tratament") {
                const idMedicam = row.dataset.idMedicam;
                body = `ID_MEDICAM=${encodeURIComponent(idMedicam)}`;
            }
            else {
                return;
            }

            fetch("controllers/" + selectedCollection.charAt(0).toUpperCase() + selectedCollection.slice(1) + "Controller.php", {
                method: "DELETE",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: body
            })
                .then(res => res.json())
                .then(data => {
                    if (data.deletedCount > 0) {
                        row.remove();
                    } else {
                        alert("Eroare: Documentul nu a fost șters.");
                    }
                })
                .catch(error => {
                    alert("Eroare la ștergere: " + error.message);
                });
        });
    });
}

// Functie pentru incarcarea pacientilor alocati unui medic
function loadPacientiByMed(idMed) {
    fetch("controllers/Alocare_pacientiController.php?pacientiMed=" + encodeURIComponent(idMed))
        .then(response => response.text())
        .then(html => {
            tableContainer.innerHTML = html;
        })
        .catch(error => {
            tableContainer.innerHTML = `<p style='color:red;'>Eroare: ${error.message}</p>`;
        });
}


