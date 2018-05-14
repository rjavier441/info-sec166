USE infosec166;
SET SQL_SAFE_UPDATES = 0;

-- WARNING: This script assumes that you have only recently launched the accompanying
-- infosec166.sql script, initializing an EMPTY database. If the database already has
-- data in it, this script may fail!!!

-- Sample User Data
INSERT INTO user(username,birthdate,password,bio) VALUES
("admin", "1995-11-04", "4813494d137e1631bba301d5acab6e7bb7aa74ce1185d456565ef51d737677b2", "I am the administrator. I am the law!"),
("stark5", "1970-05-29", "9807825d8800feac419d99af760d9a7d9c03ea4e0cb7429bd953b50710dffc40", "I am ironman."),
("batman", "1939-05-27", "c18615a6c8272bcf8d1f7fb9077810f02d3666eff8e69712cc30ba04e4f3dd63", "Fighting crime, all the time!");

-- Sample Admin Data
INSERT INTO admin(userid) VALUES (1);	-- User "admin"'s user ID is auto-incremented to 1

-- Sample Session Data
-- NOTHING. Session data is generated ONLY when a person logs in, and is removed when a person logs out!

-- Sample Post Data
INSERT INTO post(userid,title,content) VALUES
(1,"TestPost","This is a test post!"),
(2,"We have a hulk","My favorite line in the movie!"),
(3,"A script of Batman","FADE IN:

     EXT. CITYSCAPE - NIGHT

     The place is Gotham City. The time, 1987 -- once removed.

     The city of Tomorrow: stark angles, creeping shadows, 
     dense, crowded, airless, a random tangle of steel and 
     concrete, self-generating, almost subterranean in its 
     aspect... as if hell had erupted through the sidewalks and 
     kept on growing. A dangling fat moon shines overhead, ready 
     to burst.

     EXT. CATHEDRAL - NIGHT

     Amid the chrome and glass sits a dark and ornate Gothic 
     anomaly: old City Cathedral, once grand, now abandoned -- 
     long since boarded up and scheduled for demolition.

     On the rooftop far above us, STONE GARGOYLES gaze down from 
     their shadowy, windswept perches, keeping monstrous watch 
     over the distant streets below, sightless guardians of the 
     Gotham night.

     One of them is moving.

     EXT. GOTHAM SQUARE - NIGHT

     The pulsing heart of downtown Gotham, a neon nightmare of 
     big-city corruption, almost surreal in its oppressiveness. 
     Hookers wave to drug dealers. Street hustlers slap high-
     fives with three-card monte dealers. They all seem to know
     each other... with one conspicuous exception:

     A TOURIST FAMILY, Mom, Dad, and little Jimmy, staring 
     straight  ahead as they march in perfect lockstep down the 
     main drag. They've just come out of a bit show two blocks 
     over; the respectable theatre crowd has thinned out, and 
     now -- Playbills in hand -- they find themselves adrift in 
     the predatory traffic of Gotham's meanest street.

                              MOM
               For God's sake, Harold, can we
               please just get a taxi??

                              DAD
               I'm trying to get a --
                     (shouting)
               TAXI!!

     Three cabs streak pass and disappear. MOM grimaces in 
     frustration as LITTLE JIMMY consults a subway map.

                              JIMMY
               We're going the wrong way.

     Nearby, STREET TYPES are beginning to snicker. DAD surveys 
     them nervously, gestures toward the subway map.

                              DAD
               Put that away. We'll look like
               tourists.

     TWO COPS lean on their patrol car outside an all-night 
     souvlaki stand, sipping coffee and chatting with a HOOKER. 
     The HOOKER smiles at JIMMY.  JIMMY smiles back. MOM yanks 
     him off down the street and glowers at DAD.

                              DAD (cont.)
               We'll never get a cab here. Let's
               cut over to Seventh.

                              JIMMY
               Seventh is that way.

                              DAD
               I know where we are!

     EXT. SIDE STREET - THAT MOMENT - NIGHT

     A deserted access street, sidewalks lined with the husks of 
     stripped-down cars. MOM, DAD, and JIMMY take a deep breath 
     and march down the darkened street. A VOICE startles them.

                              VOICE
               Hey, mister. Gimme a dollar?

     The VOICE belongs to a DERELICT -- nineteen or twenty, 
     acne-scarred -- who sits between two garbage cans, his palm 
     uplifted. His ratty t-shirt reads: 'I LOVE GOTHAM CITY.'

     MOM, DAD, and JIMMY pause for the merest of seconds, then 
     move on -- pretending not to hear.

                              DERELICT
               Mister. How about it. One dollar?
                     (standing up)
               One dollar, man. Are you deaf?
               Are you deaf? -- Do you speak
               English??

     By now the TOURISTS are halfway across the street. 
     Mercifully, the DERELICT doesn't seem to be following.

     They pick up their pace. They don't see the SHADOWY FIGURE 
     in the alleyway. They don't see the GUN until a gloved hand 
     brings it down, butt-first, across the back of DAD's neck.

     DAD crumples. MOM grabs JIMMY and backs up against a brick 
     wall, too terrified to scream. The DERELICT races across 
     the street to join his confederate, the STREET PUNK, who's 
     already searching for DAD's wallet.

     MOM's mouth opens in panic. They can see she's about to 
     snap -- so  the STREET PUNK, still in a crouch, trains his 
     gun on JIMMY.

                              STREET PUNK
               Do the kid a favor, lady. Don't
               scream.

     The poor woman is utterly horrified. TEARS stream down her 
     face. But she keeps her wits about her, stifles the urge to 
     shriek, and hustles JIMMY off down the street.

     The two PUNKS watch them break into a run -- then chuckle, 
     slap hands, race off in the opposite direction.

     EXT. ROOFTOP - NIGHT

     Six stories up. The PUNKS -- NICK and EDDIE -- hunker down 
     on the tar-and-gravel roof, sizing up their take.

                              NICK
                     (emptying the wallet)
               All right. The Gold Card.
                     (tossing the credit card
                     in EDDIE's face)
               Don't leave home without it.

     A chill wind whips across the roof as NICK extracts the 
     cash and begins to count it. There's a distant, indistinct 
     CLANG: metal on metal. EDDIE hears it and tenses up.

                              EDDIE
               Let's beat it, man. I don't like
               being up here.

                              NICK
               What, scared of heights?

                              EDDIE
               I dunno, man. After what happened to
               Johnny Gobs --

                              NICK
               Look, Johnny Gobs got ripped and
               walked off a roof, all right? No big
               loss.

                              EDDIE
               That ain't what I heard. That ain't
               what I heard at all.
                     (beat)
               I heard the bat got him.

                              NICK
               Gimme a break, will you? Shut up... 

                              EDDIE
               Five stories, straight down. There
               was no blood in the body.

                              NICK
               No shit. It was all over the
               pavement.

     NICK has no patience with campfire tales -- but here on the 
     roof, in the pale moonlight, he can't ignore the slight 
     tingle at the base of his spine... 

                              EDDIE
               There was no blood, man.
                     (beat)
               My brother says... all the bad things
               you done... they come back and
               haunt you... 

                              NICK
               Listen to this. How old are you?
               There ain't no bat.

                              EDDIE
               My brother's a priest, man.

                              NICK
               No wonder you're such a chickenshit.
               Now shut up.
                     (conclusively)
               There ain't no bat.

     As they speak our attention shifts to a point at the 
     opposite corner of the roof, some fifteen yards away... 
     where, at the end of a line, a STRANGE BLACK SILHOUETTE is 
     dropping slowly, implacably, into frame... 

                              EDDIE
               You shouldn'ta turned the gun on
               that kid, man. You shouldn'ta --

                              NICK
               Do you want this money or don't
               you? Now shut up! Shut up --

     BOTH PUNKS FREEZE at the sudden, inexplicable sound of 
     BOOTS CRUNCHING ON GRAVEL. They turn slowly. Their JAWS 
     DROP.

     Standing at the edge of the roof, bathed in moonlight, is a 
     BLACK APPARITION. IT DOES NOT MOVE.

     EDDIE stands rooted to the spot, a choked gurgle in his 
     throat, as if he's just seen his own death. The BLACK 
     FIGURE advances, spreading  its arms. Or rather, its WINGS: 
     GREAT BLACK BATWINGS, flapping in the wind.

     NICK drops to the gravel, gropes for the gun, brings it up.

     And still the BLACK FIGURE draws closer, deliberate, 
     menacing. On its chest: THE EMBLEM OF A BAT, in an oval 
     yellow field, glowing like a target in the darkness... 

     NICK FIRES TWICE. TWO CLEAN HITS. The strange black figure 
     is knocked bodily to the roof.

     Trembling, sweating buckets, NICK gets to his feet. He 
     whacks a motionless EDDIE on the arm --

                              NICK (cont.)
               I'm gettin' outta here.

     -- and bends to retrieve his loot. EDDIE lets out a 
     strange, pre-verbal squeal... 

     ... and NICK sees THE HUMAN BAT, BACK ON ITS FEET, 
     NIGHTMARISH, UNDEAD, MOVING SLOWLY AND INEVITABLY CLOSER.

     Panic. Sheer, raw, unrelenting panic. Stolen money flutters 
     out of NICK's hands. He scuttles around the periphery of 
     the roof, his feet skidding on the gravel as he searches 
     for a way down. The BLACK SPECTRE is blocking his path to 
     the fire escape. Trapped like a rat, NICK FIRES WILDLY.

     EDDIE is frozen in place, his eyes glazed over, his face 
     drained of blood. The BAT treads calmly past. A LEG snakes 
     out. A BLACK BOOT catches EDDIE high on the chest --

     -- LIFTS HIM CLEANLY OFF HIS FEET --

     -- AND SENDS HIM FLYING THROUGH THE AIR. EDDIE slams into a 
     brick chimney and slumps to the roof unconscious, a broken, 
     weightless puppet.

     THIS ACTION IS SO SMOOTH, SO AUTOMATIC, THAT THE BAT DOES 
     NOT EVEN BREAK HIS STRIDE. NICK sees his chance and CHARGES 
     past the black wraith, scrambling toward the fire escape... 

     A GLOVED HAND slices through the air, and NICK pitches 
     forward, his legs ensnared in a tangle of WIRES. Screaming 
     now, he drags himself across the gravel roof, the looming 
     figure of the BAT at his heels... 

     ... until there's no place left to go. NICK cowers against 
     the ledge, his pants torn, his hands and knees bloody. He 
     has dissolved into total mindless hysteria.

     Almost by reflex, NICK keeps shooting. He'd do better if he 
     could manage to open his eyes. By now the hammer is falling 
     on an empty chamber, but NICK continues, obsessively, to 
     pull the trigger. He weeps; he moans; he wails... 

     THE BAT grabs a fistful of NICK's shirt, and with 
     supernatural ease HOISTS HIM into the air.");

-- Sample Topic Data
INSERT INTO topic (name,description) VALUES
("Film","The post is about a film, or the more general topic of the art of film."),
("Rant","The post contains verbal banter and often strong words about a particular theme, topic, or other entity."),
("Test","The post is a test"),
("Announcement","The post is meant for the general user base and should be read by all.");

-- Sample Post Topic Data
INSERT INTO posttopic (postid,topicid) VALUES
(1,3),
(2,1),
(3,1);

SET SQL_SAFE_UPDATES = 1;