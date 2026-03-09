/**
 * TableSortUtils.js – Client-seitige Tabellensortierung
 *
 * Nutzung: data-sort-key="..." an <th>-Elemente setzen.
 * Optional: data-sort-type="number" für numerische Sortierung.
 *
 * Initialisierung:
 *   initSortableTable(document.querySelector('.data-table'));
 */

/**
 * Macht eine Tabelle per Klick auf Spaltenheader sortierbar.
 * @param {HTMLTableElement} table - Die Tabelle
 */
function initSortableTable(table) {
    if (!table) return;

    const thead = table.querySelector('thead');
    const tbody = table.querySelector('tbody');
    if (!thead || !tbody) return;

    const headers = thead.querySelectorAll('th[data-sort-key]');
    if (headers.length === 0) return;

    // Spaltenindex-Mapping: data-sort-key → Spaltenindex
    const allThs = Array.from(thead.querySelectorAll('th'));
    const keyToIndex = {};
    allThs.forEach(function (th, idx) {
        const key = th.getAttribute('data-sort-key');
        if (key) keyToIndex[key] = idx;
    });

    // Aktuelle Sortierung
    let currentSortKey = null;
    let currentDirection = 'asc';

    headers.forEach(function (th) {
        th.addEventListener('click', function () {
            const sortKey = th.getAttribute('data-sort-key');
            const sortType = th.getAttribute('data-sort-type') || 'text';
            const colIndex = keyToIndex[sortKey];

            if (colIndex === undefined) return;

            // Richtung togglen
            if (currentSortKey === sortKey) {
                currentDirection = currentDirection === 'asc' ? 'desc' : 'asc';
            } else {
                currentSortKey = sortKey;
                currentDirection = 'asc';
            }

            // Pfeil-Indikatoren aktualisieren
            headers.forEach(function (h) {
                h.classList.remove('sort-asc', 'sort-desc');
            });
            th.classList.add(currentDirection === 'asc' ? 'sort-asc' : 'sort-desc');

            // Zeilen sortieren
            var rows = Array.from(tbody.querySelectorAll('tr'));

            // Index der ersten text-sortierbaren Spalte als Sekundärsortierung
            var firstTextIndex = null;
            allThs.some(function (h, idx) {
                if (h.getAttribute('data-sort-key') && h.getAttribute('data-sort-type') !== 'number') {
                    firstTextIndex = idx;
                    return true;
                }
                return false;
            });

            rows.sort(function (a, b) {
                var result = compareRows(a, b, colIndex, sortType);
                // Sekundärsortierung bei Gleichheit
                if (result === 0 && firstTextIndex !== null && firstTextIndex !== colIndex) {
                    result = compareRows(a, b, firstTextIndex, 'text');
                }
                return currentDirection === 'asc' ? result : -result;
            });

            // DOM neu anordnen
            rows.forEach(function (row) {
                tbody.appendChild(row);
            });
        });
    });
}

/**
 * Vergleicht zwei Zeilen anhand einer Spalte.
 */
function compareRows(rowA, rowB, colIndex, sortType) {
    var cellA = rowA.children[colIndex];
    var cellB = rowB.children[colIndex];

    if (!cellA || !cellB) return 0;

    var valA = (cellA.textContent || '').trim();
    var valB = (cellB.textContent || '').trim();

    if (sortType === 'number') {
        var numA = parseFloat(valA.replace(/[^\d.,-]/g, '').replace(',', '.')) || 0;
        var numB = parseFloat(valB.replace(/[^\d.,-]/g, '').replace(',', '.')) || 0;
        return numA - numB;
    }

    // Text: locale-sensitive Vergleich (Deutsch)
    return valA.localeCompare(valB, 'de', { sensitivity: 'base' });
}
