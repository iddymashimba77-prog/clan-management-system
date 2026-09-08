document.addEventListener("DOMContentLoaded", function () {

    const data = window.FAMILY_TREE_DATA || {};
    const members = data.members || {};
    const roots = data.roots || [];

    const tree = document.getElementById("familyTree");
    const canvas = document.getElementById("familyTreeCanvas");

    const zoomInBtn = document.getElementById("zoomIn");
    const zoomOutBtn = document.getElementById("zoomOut");
    const resetBtn = document.getElementById("treeReset");
    const zoomLevel = document.getElementById("zoomLevel");
    const searchInput = document.getElementById("treeSearch");

    let scale = 1;
    let posX = 0;
    let posY = 0;

    let dragging = false;
    let startX = 0;
    let startY = 0;


    /* ==========================
       NAME
    ========================== */

    function memberName(member) {

        return [
            member.first_name,
            member.middle_name,
            member.last_name
        ]
        .filter(Boolean)
        .join(" ")
        .replace(/\s+/g, " ")
        .trim();
    }


    /* ==========================
       PERSON CARD
    ========================== */

    function createPerson(member) {

        const card = document.createElement("div");

        const genderClass =
            member.gender === "Male"
                ? "male"
                : "female";

        card.className = `tree-person ${genderClass}`;

        card.dataset.memberId = member.user_id;
        card.dataset.name = memberName(member).toLowerCase();
        card.dataset.code = member.member_code.toLowerCase();

        let photo = "../../assets/img/default-avatar.png";

        if (member.profile_picture) {
            photo =
                "../../uploads/profile/" +
                member.profile_picture;
        }

        card.innerHTML = `

            <img
                class="tree-avatar"
                src="${escapeHtml(photo)}"
                onerror="this.src='../../assets/img/default-avatar.png'"
            >

            <div class="tree-info">

                <strong>
                    ${escapeHtml(memberName(member))}

                    ${
                        Number(member.is_deceased) === 1
                            ? `<span class="deceased-mark">
                                <i class="fas fa-star"></i>
                              </span>`
                            : ""
                    }
                </strong>

                <small class="member-code">
                    ${escapeHtml(member.member_code)}
                </small>

                <small>
                    ${escapeHtml(member.gender)}
                </small>

            </div>
        `;

        return card;
    }


    /* ==========================
       COUPLE
    ========================== */

    function createCouple(member) {

        const wrapper = document.createElement("div");
        wrapper.className = "tree-root";

        const couple = document.createElement("div");
        couple.className = "tree-couple";

        const first = createPerson(member);

        couple.appendChild(first);


        const spouseId = Number(member.spouse_user_id || 0);

        let spouse = null;

        if (spouseId && members[spouseId]) {
            spouse = members[spouseId];
        }


        if (spouse) {

            const marriage = document.createElement("div");
            marriage.className = "marriage-line";

            couple.appendChild(marriage);
            couple.appendChild(createPerson(spouse));
        }


        wrapper.appendChild(couple);


        /*
         * Children are collected from both parents.
         */
        const children = getChildren(member, spouse);

        if (children.length > 0) {

            const childrenArea = document.createElement("div");
            childrenArea.className = "children-area";

            children.forEach(child => {

                const branch = document.createElement("div");
                branch.className = "child-branch";

                const childTree = createCouple(child);

                branch.appendChild(childTree);

                childrenArea.appendChild(branch);
            });

            wrapper.appendChild(childrenArea);
        }

        return wrapper;
    }


    /* ==========================
       CHILDREN
    ========================== */

    function getChildren(parent, spouse) {

        const result = {};

        Object.values(members).forEach(member => {

            const father =
                Number(member.father_user_id || 0);

            const mother =
                Number(member.mother_user_id || 0);

            const parentId =
                Number(parent.user_id);

            const spouseId =
                spouse
                    ? Number(spouse.user_id)
                    : 0;

            if (
                father === parentId ||
                mother === parentId ||
                father === spouseId ||
                mother === spouseId
            ) {
                result[member.user_id] = member;
            }
        });

        return Object.values(result);
    }


    /* ==========================
       ROOTS
    ========================== */

    function renderTree() {

        tree.innerHTML = "";

        if (!roots.length) {

            tree.innerHTML = `
                <div style="
                    padding:50px;
                    color:#667085;
                    text-align:center;
                ">
                    No family relationships found.
                </div>
            `;

            return;
        }


        const used = new Set();


        roots.forEach(root => {

            if (used.has(root.user_id)) {
                return;
            }

            tree.appendChild(
                createCouple(root)
            );

            used.add(root.user_id);

            if (root.spouse_user_id) {
                used.add(Number(root.spouse_user_id));
            }
        });


        applyTransform();
    }


    /* ==========================
       ESCAPE
    ========================== */

    function escapeHtml(value) {

        return String(value)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }


    /* ==========================
       ZOOM
    ========================== */

    function applyTransform() {

        tree.style.transform =
            `translate(${posX}px, ${posY}px) scale(${scale})`;

        zoomLevel.textContent =
            Math.round(scale * 100) + "%";
    }


    zoomInBtn.addEventListener("click", function () {

        scale = Math.min(scale + 0.1, 2);

        applyTransform();
    });


    zoomOutBtn.addEventListener("click", function () {

        scale = Math.max(scale - 0.1, 0.4);

        applyTransform();
    });


    resetBtn.addEventListener("click", function () {

        scale = 1;
        posX = 0;
        posY = 0;

        searchInput.value = "";

        document
            .querySelectorAll(".tree-person")
            .forEach(card =>
                card.classList.remove("highlight")
            );

        applyTransform();
    });


    /* ==========================
       DRAG
    ========================== */

    canvas.addEventListener("mousedown", function (e) {

        dragging = true;

        canvas.classList.add("dragging");

        startX = e.clientX - posX;
        startY = e.clientY - posY;
    });


    document.addEventListener("mousemove", function (e) {

        if (!dragging) return;

        posX = e.clientX - startX;
        posY = e.clientY - startY;

        applyTransform();
    });


    document.addEventListener("mouseup", function () {

        dragging = false;

        canvas.classList.remove("dragging");
    });


    /* ==========================
       MOUSE WHEEL ZOOM
    ========================== */

    canvas.addEventListener(
        "wheel",
        function (e) {

            e.preventDefault();

            if (e.deltaY < 0) {
                scale = Math.min(scale + 0.08, 2);
            } else {
                scale = Math.max(scale - 0.08, 0.4);
            }

            applyTransform();
        },
        { passive: false }
    );


    /* ==========================
       SEARCH
    ========================== */

    searchInput.addEventListener("input", function () {

        const query =
            this.value.trim().toLowerCase();

        document
            .querySelectorAll(".tree-person")
            .forEach(card => {

                card.classList.remove("highlight");

                if (!query) return;

                const name = card.dataset.name || "";
                const code = card.dataset.code || "";

                if (
                    name.includes(query) ||
                    code.includes(query)
                ) {
                    card.classList.add("highlight");
                }
            });
    });


    renderTree();

});