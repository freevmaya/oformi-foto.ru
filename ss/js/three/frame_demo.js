var fdemo = function(models, width, height, alternative) {
    if ( ! Detector.webgl ) Detector.addGetWebGLMessage();
    
    var container;
    var camera, scene, renderer, hemiLight;
    var yRoll = 0;
    var moveTo = {x:0, y:0};
    var meshList = [];
    var infoList = [];
    var curMesh = -1;
    var loadCount = 0;
    var stepX = 3000;
    var meshSize = 600;
    var camDistance = 1800; 
    var focusPos = {x:0,y:0};
    var clock = new THREE.Clock();
    var isFrameStepping = false;
    var container = $('fd-container');    
    var leftButton = $('fc-left');
    var rightButton = $('fc-right');
    var titleElem = $('fc-title');
    var descElem = $('fc-desc');
    var clickArea = $('fc-clickarea');
    var _focus = true;            
    var _rollIndex=0;
    
    var titleFX;
    var titlePos = [titleElem.getCoordinates(titleElem.getParent()),descElem.getCoordinates(descElem.getParent())];
    var waitTimer = (function() {
        $('slider3d').getParent().set('html', alternative);
    }).delay(4000);
    
    var TitleFx = new Class({
        Extends: Fx,
        isSetText: false,
        initialize: function(options) {
            this.parent(options);
        },
        
        onStart: function(){
		    this.parent();
            titleFX = this;
            this.isSetText = false;
        },
        
    	set: function(now){
            
            titleElem.setPosition({x: titlePos[0].left, y: titlePos[0].top - (50 * (1 - now))});
//            descElem.setPosition({x: titlePos[1].left, y: titlePos[1].top + (300 * (1 - now))});
            descElem.setPosition({x: titlePos[1].left - (titlePos[1].width * (1 - now)), y: titlePos[1].top});
            if ((now > 0.3) && !this.isSetText) {
                titleElem.set('text', this.options.info.title);
                descElem.set('text', this.options.info.desc);
                this.isSetText = true;
            }
    		return now;
    	},
        
        onComplete: function(){
            titleFX = null;
            this.parent();
    	}   
    });
    
    init();
    
    function init() {
        clickArea.addEvent('click', onClickArea);
        for (var i=0; i<models.length;i++) meshList.push(null);
    
    	scene = new THREE.Scene();
    	scene.add(new THREE.AmbientLight(0x666666));
        
    //----------------LIGHT-----------------
    
        var light = new THREE.DirectionalLight( 0xffffff, 1.3);
    	light.position.set(0, 100, 1000 );
    	scene.add( light );
        
    	hemiLight = new THREE.HemisphereLight(  0xffffff, 0xffffff, 0.4);
    	hemiLight.color.setHSL( 0.6, 1, 0.6 );
    	hemiLight.groundColor.setHSL( 0.095, 1, 0.75 );
    	hemiLight.position.set( 0, 500, 0);
    	//scene.add( hemiLight );
        
    //----------------                
    
    	renderer = new THREE.WebGLRenderer( { antialias: true, alpha: false } );
    	renderer.setClearColor( 0x777777 );
    	renderer.setPixelRatio( window.devicePixelRatio );
    	renderer.autoClear = false; 
    
    	container.appendChild( renderer.domElement );
    
    	scene.fog = new THREE.Fog(0xffffff, camDistance, camDistance * 1.5);
    	scene.fog.color.setHSL( 0.6, 0, 1 );
        
        function loadModel(modelInfo, index) {
            var loader = new THREE.JSONLoader();
            loader.load(modelInfo.url, function(geometry, materials) {
                doLoadModel(geometry, materials, modelInfo, index);
            });
        }
        
        for (var i=0; i<models.length; i++) loadModel(models[i], i);
        
        leftButton.addEvent('click', onLeftClick);
        rightButton.addEvent('click', onRightClick);
        
        resize(width, height);
    }
    
    function resize(width, height) {
        var ssize = {
            width: width,
            height: height
        };
        
        var bsize = leftButton.getSize();
        
        var bstyle = {
            'margin-top': Math.round((height - bsize.y) / 2)
        }
        
        leftButton.setStyles(bstyle);
        rightButton.setStyles(bstyle);
        
        clickArea.setStyles({
            "margin-left": bsize.x,
            width: width - bsize.x * 2,
            height: height
        })
        
        $('slider3d').setStyles(ssize);
        $('con-overlay').setStyles(ssize);
        var dr = descElem.getCoordinates(descElem.getParent());
        
        titleElem.setStyles({
            width: width - dr.left * 2
        });
        descElem.setStyles({
            width: width - dr.left * 2,
            height: height
        });
        container.setStyles(ssize);
        
        if (camera) {
        	camera.aspect = width / height;
        	camera.updateProjectionMatrix();
        }
    	renderer.setSize(width, height);
    }           
    
    function onClickArea() {
        var link = infoList[curMesh].link;
        if (link) document.location.href = link;
    }
    
    function copyVector(vector) {
        var result = new THREE.Vector3();
        result.set(vector.x, vector.y, vector.z);
        return result; 
    }
    
    function updateTitles() {
        if (titleFX) titleFX.cancel();
        
        titleFX = new TitleFx({
            transition: Fx.Transitions.Back.easeOut,
            fps: 16,
            duration: 500,
            info: infoList[curMesh]
        });
        
        titleFX.start(0, 1);
    }
    
    function updateButtons() {
        if (curMesh == 0) leftButton.fade(0);
        else leftButton.fade(0.6);
        if (curMesh == meshList.length - 1) rightButton.fade(0);
        else rightButton.fade(0.6);
    }
    
    function onLeftClick() {
        setCurMesh(curMesh - 1);
    }
    
    function onRightClick() {
        setCurMesh(curMesh + 1);
    }
    
    function doLoadModel(geometry, materials, modelInfo, index) {
        var mesh = new THREE.Mesh( geometry, new THREE.MeshFaceMaterial( materials ) );
        var radius  = mesh.geometry.boundingSphere.radius;
        var px = (index - (models.length - 1) / 2) * stepX;
        var scale = meshSize / radius;                 
    	mesh.position.set(px, meshSize, 0);
        mesh.scale.set(scale, scale, scale);
        meshList[index] = mesh;
        infoList[index] = modelInfo;
        
    	scene.add(mesh);
        loadCount++;
        if (loadCount == models.length) {
            setCurMesh(0);
            focusPos = copyVector(meshList[curMesh].position);
            start();
        }
    }         
    
    function setCurMesh(index) {
        if ((curMesh != index) && (index >= 0) && (index < meshList.length)) {
            curMesh = index;
            updateButtons();
            updateTitles();
        }
    } 
    
    function onMouseMove( event ) {
        if (_focus) {     
            var rect = container.getCoordinates();
            moveTo.y = limitY((rect.height * 0.8 - (event.pageY - rect.top)) / rect.height * meshSize * 2);
            moveTo.x = (rect.width * 0.5 - (event.pageX - rect.left)) / rect.width * meshSize * 0.2;
        }
    };  
    
    function start() {
        $('slider3d').setStyle('visibility', 'visible');
 // SKYDOME
    	var vertexShader = document.getElementById( 'vertexShader' ).textContent;
    	var fragmentShader = document.getElementById( 'fragmentShader' ).textContent;
    	var uniforms = {
    		topColor: 	 { type: "c", value: new THREE.Color( 0x000033 ) },
    		bottomColor: { type: "c", value: new THREE.Color( 0xffffff ) },
    		offset:		 { type: "f", value: 0 },
    		exponent:	 { type: "f", value: 0.8}
    	};
        
    	uniforms.topColor.value.copy( hemiLight.color );
    
    	scene.fog.color.copy( uniforms.bottomColor.value );
    
    	var skyGeo = new THREE.SphereGeometry(6000, 32, 15);
    	var skyMat = new THREE.ShaderMaterial({ vertexShader: vertexShader, fragmentShader: fragmentShader, uniforms: uniforms, side: THREE.BackSide });
    
    	var sky = new THREE.Mesh( skyGeo, skyMat );
    	scene.add( sky );
//-------/SKYDOME         

        var aspect = width / height;
    
    	camera = new THREE.PerspectiveCamera( 45, aspect, 1, 10000 );
    	camera.position.set(0.0, 0.0, camDistance);
        moveTo.y = camera.position.y;
    
        clearTimeout(waitTimer);
                
        document.addEventListener('mousemove', onMouseMove, false );
        window.addEventListener('blur', function(e) {
            _focus = false;
        });
        window.addEventListener('focus', function(e) {
            _focus = true;
        });
        
        render();
    }
    
    function limitY(y) {
        if (y < 0) y = 0;
        else if (y > meshSize * 3) y = meshSize * 3;
        return y;
    }
    
    function render() {
        if (_focus) {    
        	requestAnimationFrame(render, renderer.domElement );
        	var delta = clock.getDelta();
            
            var info = infoList[curMesh];
            var mesh = meshList[curMesh];             
            
            mesh.rotation.y += 0.02;
            
            var speed = 2;
            
            focusPos.x += (mesh.position.x - focusPos.x) * speed * delta; 
            focusPos.y += (mesh.position.y - focusPos.y) * speed * delta; 
            focusPos.z += (mesh.position.z - focusPos.z) * speed * delta; 
            
            var p  = camera.position;
            var y = p.y + (moveTo.y - p.y) * speed * delta;
            var x = p.x + ((focusPos.x + moveTo.x) - p.x) * 0.25;
            
            camera.position.setY(y);
            camera.position.setX(x);
            camera.lookAt(focusPos);
        	renderer.render( scene, camera );
        } else render.delay(1000/10);
    }
}