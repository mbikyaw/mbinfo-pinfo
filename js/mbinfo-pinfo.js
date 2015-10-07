/**
 * Created by mbikyaw on 7/10/15.
 */

jQuery(function() {

    var dispProtein = function(p) {
        var root = document.querySelector('.widget_pinfo .protein-box');
        var ul = document.querySelector('.widget_pinfo UL.protein-list');
        root.innerHTML = '<h5></h5>' +
            '<div class="summary"></div>' +
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

    var dispUniprot = function(uniprot) {
        for (var i = 0; i < PInfoProtein.length; i++) {
            var p = PInfoProtein[i];
            if (p.uniprot == uniprot) {
                dispProtein(p);
                return;
            }
        }
    };

    jQuery('.widget_pinfo UL.protein-list').on('click', 'A', function(ev) {
        ev.preventDefault();
        var href = ev.target.getAttribute('href');
        dispUniprot(href.substring(9, href.length - 1));
    })
});
