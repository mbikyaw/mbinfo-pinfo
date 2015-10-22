/**
 * Created by mbikyaw on 7/10/15.
 */

jQuery(function() {

    var dispProtein = function(p) {
        if (!p) {
            return;
        }
        var root = document.querySelector('.widget_pinfo .protein-box');
        var ul = document.querySelector('.widget_pinfo UL.protein-list');
        root.innerHTML = '<h5></h5>' +
            '<div class="summary"></div>' +
            '<p>More Links</p>' +
            '<div>' +
            '<a class="uniprot" target="uniprot"></a>' +
            '<a class="quickgo" target="quickgo"></a>' +
            '<a class="pfam" target="pfam"></a>' +
            '<a class="pdb" target="pdb"></a>' +
            '<a class="ncbi-protein" target="ncbi"></a>' +
            '<a class="ncbi-gene" target="ncbi"></a>' +
            '<a class="homologene" target="ncbi"></a>' +
            '</div>';

        root.querySelector('h5').textContent = p.protein;
        var summary = root.querySelector('.summary');
        summary.textContent = p.summary;

        var uniprot = root.querySelector('.uniprot');
        uniprot.href = 'http://www.uniprot.org/uniprot/' + p.uniprot;
        uniprot.setAttribute('title', 'View in UniProt (' + p.uniprot + ')');

        var quickgo = root.querySelector('.quickgo');
        quickgo.href = 'http://www.ebi.ac.uk/QuickGO/GProtein?ac=' + p.uniprot;
        quickgo.setAttribute('title', 'View in QuickGO');

        var pfam = root.querySelector('.pfam');
        pfam.href = 'http://pfam.xfam.org/protein/' + p.uniprot;
        pfam.setAttribute('title', 'View in PFAM');

        var np = root.querySelector('.ncbi-protein');
        np.href = 'http://www.ncbi.nlm.nih.gov/protein/' + p.uniprot;
        np.setAttribute('title', 'View in NCBI Protein');

        var pdb = root.querySelector('.pdb');
        if (p.pdb) {
            pdb.href = 'http://www.ebi.ac.uk/pdbe/entry/pdb/' + p.pdb;
            pdb.setAttribute('title', 'View in PDB');
        } else {
            pdb.style.display = 'none';
        }

        var gene = root.querySelector('.ncbi-gene');
        if (p.gene) {
            gene.href = 'http://www.ncbi.nlm.nih.gov/gene/?term=' + p.gene;
            gene.setAttribute('title', 'View in NCBI Gene');
        } else {
            gene.style.display = 'none';
        }

        var homologene = root.querySelector('.homologene');
        if (p.gene) {
            homologene.href = 'http://www.ncbi.nlm.nih.gov/homologene?LinkName=gene_homologene&from_uid=' + p.gene;
            homologene.setAttribute('title', 'View in NCBI HomoloGene');
        } else {
            homologene.style.display = 'none';
        }

        root.style.display = '';
    };

    var findByProtein = function(protein) {
        protein = protein.toLowerCase();
        for (var i = 0; i < PInfoProtein.length; i++) {
            var p = PInfoProtein[i];
            if (!!p.protein && p.protein.toLowerCase() == protein) {
                return p;
            }
        }
    };

    var findUniprot = function(uniprot) {
        for (var i = 0; i < PInfoProtein.length; i++) {
            var p = PInfoProtein[i];
            if (p.uniprot == uniprot) {
                return p;
            }
        }
    };

    var findByFamily = function(family) {
        family = family.toLowerCase();
        for (var i = 0; i < PInfoProtein.length; i++) {
            var p = PInfoProtein[i];
            if (!!p.family && p.family.toLowerCase() == family) {
                return p;
            }
        }
    };

    var dispUniprot = function(uniprot) {
        var p = findUniprot(uniprot);
        if (p) {
            dispProtein(p);
        } else {
            alert('UniProt ID "' + (uniprot || '') + '" not found.');
        }
    };

    var dispByName = function(protein) {
        var p = findByProtein(protein);
        if (p) {
            dispProtein(p);
        } else {
            alert('Protein name "' + (protein || '') + '" not found.');
        }
    };

    var dispByFamily = function(family) {
        var p = findByFamily(family);
        if (p) {
            dispProtein(p);
        } else {
            alert('Protein family "' + (family || '') + '" not found.');
        }
    };


    var handleSelectionChanged = function(ev) {
        dispUniprot(ev.currentTarget.value);
    };

    var handleUniProtClick = function(ev) {
        ev.preventDefault();
        var href = ev.target.getAttribute('href');
        dispUniprot(href.substring(9, href.length - 1));
    };

    var handleProteinClick = function(ev) {
        ev.preventDefault();
        var href = ev.target.getAttribute('href');
        dispByName(href.substring(9, href.length - 1));
    };

    var handleFamilyClick = function(ev) {
        ev.preventDefault();
        var href = ev.target.getAttribute('href');
        dispByFamily(href.substring(8, href.length - 1));
    };

    var ups = document.querySelectorAll('A[uniprot]');
    for (var i = 0; i < ups.length; i++) {
        var a = ups[i];
        var uniprot = a.getAttribute('uniprot');
        a.href = '/uniprot/' + uniprot + '/';
        a.onclick = handleUniProtClick;
        a.classList.add('protein');
        if (!findUniprot(uniprot)) {
            a.classList.add('not-found');
        }
    }

    var proteins = document.querySelectorAll('A[protein]');
    for (var i = 0; i < proteins.length; i++) {
        var a = proteins[i];
        if (a.hasAttribute('href')) {
            continue;
        }
        var protein = a.getAttribute('protein');
        a.href = '/protein/' + protein + '/';
        a.onclick = handleProteinClick;
        a.classList.add('protein');
        if (!findByProtein(protein)) {
            a.classList.add('not-found');
        }
    }

    var familys = document.querySelectorAll('A[family]');
    for (var i = 0; i < familys.length; i++) {
        var a = familys[i];
        if (a.hasAttribute('href')) {
            continue;
        }
        var family = a.getAttribute('family');
        a.href = '/family/' + family + '/';
        a.onclick = handleFamilyClick;
        a.classList.add('protein');
        if (!findByFamily(family)) {
            a.classList.add('not-found');
        }
    }

    /**
     * Organize protein into family
     */
    var processPInfoProtein = function() {
        PInfoFamily = {};
        for (var p in PInfoProtein) {
            if (!PInfoFamily[p.family]) {
                PInfoFamily[p.family] = [];
            }
            var family = PInfoFamily[p.family];
            family.push(p);
        }

    };

    jQuery('.DefaultSidebar').stick_in_parent();

    if (typeof PInfoProtein != 'undefined') {
        var sel = document.querySelector('.widget_pinfo SELECT.protein-list');
        sel.onchange = handleSelectionChanged;

        setTimeout(function() {
            if (PInfoProtein[0]) {
                dispProtein(PInfoProtein[0]);
            }
        }, 200);
    }



});
