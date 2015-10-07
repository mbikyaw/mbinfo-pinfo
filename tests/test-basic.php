<?php



class BasicTest extends WP_UnitTestCase {

	function test_get_protein() {
		// replace this with some actual testing code
		$pinfo = new MBInfoPInfo();
		$cnt = $pinfo->insert_data(['Myosin II,"Myosins are a superfamily of molecular motors that bind to actin filaments via a conserved motor domain and produce movement along actin filaments in an ATP-dependent manner. The mammalian myosin superfamily comprises of myosin classes I, II, III, V, VI, VII, IX, X and XV, of which Myosin II are the conventional myosins, forming filaments in muscle cells and in the cytoplasm of non-muscle cells.",Myosin-9,P35579,,4627', ',,Myosin-10,P35580,4pd3,4628']);
		$this->assertEquals(1, $cnt, 'insert count');
		$p = $pinfo->get_record('P35579');
		$this->assertNotNull($p, 'found');
		$this->assertEquals($p->uniprot, 'P35579');
	}
}

