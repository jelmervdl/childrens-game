var audio_assets = {
	'conversations': [
		'1.aap.ik',
		'10.hond.hij',
		'11.olif.ik',
		'12.olif.jij',
		'13.olif.hij',
		'14.olif.ik',
		'15.olif.jij',
		'16.aap.indjij',
		'17.aap.indhij',
		'18.aap.dirik',
		'19.aap.dirjij',
		'2.aap.hij',
		'20.aap.dirhij',
		'21.aap.indik',
		'22.aap.indjij',
		'23.aap.indhij',
		'24.aap.dirik',
		'25.aap.dirjij',
		'26.hond.indik',
		'27.hond.indhij',
		'28.hond.dirjij',
		'29.hond.dirhij',
		'3.aap.ik',
		'30.hond.indik',
		'31.hond.indjij',
		'32.hond.indhij',
		'33.hond.dirik',
		'34.hond.dirjij',
		'35.hond.dirhij',
		'36.olif.indik',
		'37.olif.indjij',
		'38.olif.indhij',
		'39.olif.dirik',
		'4.aap.jij',
		'40.olif.dirjij',
		'41.olif.dirhij',
		'42.olif.indik',
		'43.olif.indjij',
		'44.olif.dirik',
		'45.olif.dirhij',
		'5.aap.hij',
		'6.hond.ik',
		'7.hond.jij',
		'8.hond.hij',
		'9.hond.jij',
		'p1.olif',
		'p2.aap',
		'p3.hond'
	],
	'cues': [
		'auto',
		'boek',
		'gitaar',
		'hamer',
		'hoed',
		'kop',
		'lepel',
		'paraplu',
		'pen',
		'roos',
		'schaar',
		'sjaal',
		'sleutel',
		'tandenborstel',
		'vlag',
		'vliegtuig',
		'voetbal',
		'zonnebril'
	],
	'decorations': [
		'magic',
		'whispering'
	],
	'instructions': [
		'instruction1',
		'instruction2',
		'instruction3',
		'intro.aap',
		'intro.hond',
		'intro.olif',
		'm1',
		'm2',
		'm3',
		'm4',
		'm4n',
		'm5',
		'm5n',
		'm6',
		'm6n',
		'm7',
		'm8',
		'o1',
		'o10',
		'o11',
		'o12',
		'o13',
		'o14',
		'o15',
		'o16',
		'o17',
		'o18',
		'o2',
		'o3',
		'o4',
		'o5',
		'o6',
		'o7',
		'o8',
		'o9'
	]
};

function find_audio_asset(name)
{
    for (var folder in audio_assets) {
		if (audio_assets[folder].indexOf(name) != -1)
			return 'www/assets/audio/' + folder + '/' + name + '.wav';
	}

	throw "Asset not found: " + name;
}