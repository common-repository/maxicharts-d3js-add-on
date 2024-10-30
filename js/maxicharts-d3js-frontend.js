jQuery(document).ready(
		function($) {

			console.log("++++ ready to show d3js...");
			// DOM element where the Timeline will be attached
			var container = document.getElementById('visualization');
			console.log("container found : " + container);
			// get attrs
			
			var category = maxicharts_d3js_ajax_object.category;//container.getAttribute('category');
			var separator = maxicharts_d3js_ajax_object.separator;//container.getAttribute('separator');
			var data_path = maxicharts_d3js_ajax_object.data_path;//container.getAttribute('data_path');
			var url = container.getAttribute('url');
			var user_groups = maxicharts_d3js_ajax_object.groups;
			var type = maxicharts_d3js_ajax_object.type;//container.getAttribute('type');
			var gf_form_id = maxicharts_d3js_ajax_object.gf_form_id;
/*
			'height' => $height,
            'width' => $width,
            'groups' => $groups,
            'type' => $type,
            'gf_form_id' => $gf_form_id,
			*/
			console.log("type found : " + type);
			console.log("data_path found : " + data_path);
			console.log("url found : " + url);
			console.log("groups found : " + user_groups);
			console.log(maxicharts_d3js_ajax_object);
			var data;
			var items = null;
			var timeline = null;
			// Create a Timeline

			var timelineOptions = {
				'height' : '500px',
				'width' : '100%',
			};

			var shortcode_height = maxicharts_d3js_ajax_object.height;
			var shortcode_width = maxicharts_d3js_ajax_object.width;
			
			
			if (shortcode_height) {
				timelineOptions = {
					'height' : shortcode_height
				}
			} else {
			    shortcode_height = '500';
			}
			if (shortcode_width) {
				timelineOptions = {
					'width' : shortcode_width
				}
			} else {
			    shortcode_width = '960';
			}
			var groups = [];
			if (user_groups != null) {
				groups_array = user_groups.split(',');
				groups_array.forEach(function(element) {
					console.log(element);
					groups.push({
						id : element,
						value : element
					});
				});

			}

			console.log(groups);
			
		 if (data_path != null) {
				// Create a DataSet (allows two way data-binding)
				var options = {
					"separator" : separator,
				};
				var dataSources = data_path.split(',');

				console.log(dataSources);

				dataSources.forEach(function(element) {
					console.log("Current source: " + element);
					// d3.select('#visualization')
					const width = shortcode_width,// window.innerWidth,
		            height = shortcode_height,// window.innerHeight,
		            maxRadius = (Math.min(shortcode_width, shortcode_height) / 2) - 5;
							
					
					if (type == "tree" ) {
						console.log(type +" "+gf_form_id);
						if (gf_form_id > 0) {
							console.log("create tree from gf form : " + type);

							var data = {
									action : 'maxicharts_get_form_as_tree',
									form_id : gf_form_id,
								
								};
								console.log(data);
								$.post(maxicharts_d3js_ajax_object.ajax_url, data,
										function(posts_list) {
										console.log("tree retrieved from backend:");
											console.log(posts_list);
								
											//processInputDatas(posts_list);
											var myTree = new MaxiChartsCollapsibleTree(null, posts_list, width, height,'#visualization', maxicharts_d3js_ajax_object);
										});
							 
					
							
						} else if (type == "tree"){
							// create tree from file - CSV or JSON
							var myTree = new MaxiChartsCollapsibleTree(element, null,  width, height,'#visualization', maxicharts_d3js_ajax_object);
						}
					} else if (type == 'sunburst'){
					
							
				        const formatNumber = d3.format(',d');
				        const x = d3.scaleLinear()
				            .range([0, 2 * Math.PI])
				            .clamp(true);

				        const y = d3.scaleSqrt()
				            .range([maxRadius*.1, maxRadius]);

				        const color = d3.scaleOrdinal(d3.schemeCategory20);

				        const partition = d3.partition();

				        const arc = d3.arc()
				            .startAngle(d => x(d.x0))
				            .endAngle(d => x(d.x1))
				            .innerRadius(d => Math.max(0, y(d.y0)))
				            .outerRadius(d => Math.max(0, y(d.y1)));

				        const middleArcLine = d => {
				            const halfPi = Math.PI/2;
				            const angles = [x(d.x0) - halfPi, x(d.x1) - halfPi];
				            const r = Math.max(0, (y(d.y0) + y(d.y1)) / 2);

				            const middleAngle = (angles[1] + angles[0]) / 2;
				            const invertDirection = middleAngle > 0 && middleAngle < Math.PI; // On
																								// lower
																								// quadrants
																								// write
																								// text
																								// ccw
				            if (invertDirection) { angles.reverse(); }

				            const path = d3.path();
				            path.arc(0, 0, r, angles[0], angles[1], invertDirection);
				            return path.toString();
				        };

				        const textFits = d => {
				            const CHAR_SPACE = 6;

				            const deltaAngle = x(d.x1) - x(d.x0);
				            const r = Math.max(0, (y(d.y0) + y(d.y1)) / 2);
				            const perimeter = r * deltaAngle;

				            return d.data.name.length * CHAR_SPACE < perimeter;
				        };

				        const svg = d3.select('#visualization').append('svg')
				            /*.style('width', '100vw')
				            .style('height', '100vh')*/
				            .style('width', shortcode_width)
                            .style('height', shortcode_height)
				            .attr('viewBox', `${-shortcode_width / 2} ${-shortcode_height / 2} ${shortcode_width} ${shortcode_height}`)
				            .on('click', () => focusOn()); // Reset zoom on
															// canvas click

				        d3.json(element, (error, root) => {
				            if (error) throw error;

				            console.log("Read Json");
				            root = d3.hierarchy(root);
				            root.sum(d => d.size);

				            const slice = svg.selectAll('g.slice')
				                .data(partition(root).descendants());

				            slice.exit().remove();

				            const newSlice = slice.enter()
				                .append('g').attr('class', 'slice')
				                .on('click', d => {
				                    d3.event.stopPropagation();
				                    focusOn(d);
				                });

				            newSlice.append('title')
				                .text(d => d.data.name + '\n' + formatNumber(d.value));

				            newSlice.append('path')
				                .attr('class', 'main-arc')
				                .style('fill', d => color((d.children ? d : d.parent).data.name))
				                .attr('d', arc);

				            newSlice.append('path')
				                .attr('class', 'hidden-arc')
				                .attr('id', (_, i) => `hiddenArc${i}`)
				                .attr('d', middleArcLine);

				            const text = newSlice.append('text')
				                .attr('display', d => textFits(d) ? null : 'none');

				            // Add white contour
				            text.append('textPath')
				                .attr('startOffset','50%')
				                .attr('xlink:href', (_, i) => `#hiddenArc${i}` )
				                .text(d => d.data.name)
				                .style('fill', 'none')
				                .style('stroke', '#fff')
				                .style('stroke-width', 5)
				                .style('stroke-linejoin', 'round');

				            text.append('textPath')
				                .attr('startOffset','50%')
				                .attr('xlink:href', (_, i) => `#hiddenArc${i}` )
				                .text(d => d.data.name);
				            
				            
				            console.log("Json all parsed");
				        });

				        function focusOn(d = { x0: 0, x1: 1, y0: 0, y1: 1 }) {
				            // Reset to top-level if no data point specified

				            const transition = svg.transition()
				                .duration(750)
				                .tween('scale', () => {
				                    const xd = d3.interpolate(x.domain(), [d.x0, d.x1]),
				                        yd = d3.interpolate(y.domain(), [d.y0, 1]);
				                    return t => { x.domain(xd(t)); y.domain(yd(t)); };
				                });

				            transition.selectAll('path.main-arc')
				                .attrTween('d', d => () => arc(d));

				            transition.selectAll('path.hidden-arc')
				                .attrTween('d', d => () => middleArcLine(d));

				            transition.selectAll('text')
				                .attrTween('display', d => () => textFits(d) ? null : 'none');

				            moveStackToFront(d);

				            //

				            function moveStackToFront(elD) {
				                svg.selectAll('.slice').filter(d => d === elD)
				                    .each(function(d) {
				                        this.parentNode.appendChild(this);
				                        if (d.parent) { moveStackToFront(d.parent); }
				                    })
				            }
				        }
							
					} else if (type == 'network' || type == 'mchartsn_node'){
						// add this :
						// https://blockbuilder.org/bumbeishvili/b96ba47ea21d14dfce6ebb859b002d3a
						
					const svg = d3.select('#visualization').append('svg')
			            .style('width', width)
			            .style('height', height)
			     
			            var color = d3.scaleOrdinal(d3.schemeCategory20);
						var radius = d3.scaleSqrt().range([1, 5]);

						function dragstarted(d) {
				            if (!d3.event.active) force.alphaTarget(0.5).restart();
				            d.fx = d.x;
				            d.fy = d.y;
				        }
				        
				        function dragged(d) {
				            d.fx = d3.event.x;
				            d.fy = d3.event.y;
				        }
				        
				        function dragended(d) {
				            if (!d3.event.active) force.alphaTarget(0.5);
				            d.fx = null;
				            d.fy = null;
				        } 
				        
				        // TODO https://bl.ocks.org/jjzieve/a743242f46321491a950
				        var contractionFactor = 10.0;
				        var directedForce = 0;
				        var tempValue = 100;// d.value
				        
				        function processInputDatas(json) {
				    		// if (error) throw error;
							console.log(json);
				            force
				                .nodes(json.nodes) // .on("tick", tick)
				                .force("link").links(json.links);
				             
				                
				                var link = svg.selectAll(".link")
				                .data(json.links)
				                .enter()
				                .append("line")
				                .attr("class", "link");

				            var node = svg.selectAll(".node")
				                .data(json.nodes)
				                .enter().append("g")
				                .attr("class", "node")
				                .call(d3.drag()
				                .on("start", dragstarted)
				                .on("drag", dragged)
				                .on("end", dragended));  

				            
				            const mouseOverFunction = function (d) {
					        	  const circle = d3.select(this);

					        	  node
					        	    .transition(500)
					        	      .style('opacity', o => {
					        	        const isConnectedValue = isConnected(o, d);
					        	        if (isConnectedValue) {
					        	          return 1.0;
					        	        }
					        	        return 0.2
					        	      })
					        	      .style('fill', (o) => {
					        	        let fillColor;
					        	        if (isConnectedAsTarget(o, d) && isConnectedAsSource(o, d)) {
					        	          fillColor = 'green';
					        	        } else if (isConnectedAsSource(o, d)) {
					        	          fillColor = 'red';
					        	        } else if (isConnectedAsTarget(o, d)) {
					        	          fillColor = 'blue';
					        	        } else if (isEqual(o, d)) {
					        	          fillColor = 'hotpink';
					        	        } else {
					        	          fillColor = '#000';
					        	        }
					        	        return fillColor;
					        	      });

					        	  link
					        	    .transition(500)
					        	      .style('stroke-opacity', o => (o.source === d || o.target === d ? 1 : 0.2))
					        	      .transition(500)
					        	      .attr('marker-end', o => (o.source === d || o.target === d ? 'url(#arrowhead)' : 'url()'));

					        	  circle
					        	    .transition(500)
					        	      .attr('r', () => 1.4 *radius(d.r / contractionFactor));
					        	};

					        	const mouseOutFunction = function (d) {
					        	  const circle = d3.select(this);

					        	  node
					        	    .transition(500);

					        	  link
					        	    .transition(500);

					        	  circle
					        	    .transition(500)
					        	      .attr('r', radius(d.r / contractionFactor));
					        	};
					        	
					        	
					        	function isConnected(a, b) {
					        	  return isConnectedAsTarget(a, b) || isConnectedAsSource(a, b) || a.index === b.index;
					        	}

					        	function isConnectedAsSource(a, b) {
					        	  return linkedByIndex[`${a.index},${b.index}`];
					        	}

					        	function isConnectedAsTarget(a, b) {
					        	  return linkedByIndex[`${b.index},${a.index}`];
					        	}

					        	function isEqual(a, b) {
					        	  return a.index === b.index;
					        	}
				            
				            
				            node.append('circle')
				             .attr("r", function(d) { return radius(d.r / contractionFactor); })
      .attr("fill", function(d) { return color(); })
      .on('mouseover', mouseOverFunction)
    .on('mouseout', mouseOutFunction);
				               

				            
				            node.append("text")
				            .attr("dy", ".35em")
				            .style('fill', 'white')				            
				            .attr("text-anchor", "middle")
				            .text(function(d) { return d.name; });

				            
				            force.on("tick", function () {
				                link.attr("x1", function (d) {
				                        return d.source.x;
				                    })
				                    .attr("y1", function (d) {
				                        return d.source.y;
				                    })
				                    .attr("x2", function (d) {
				                        return d.target.x;
				                    })
				                    .attr("y2", function (d) {
				                        return d.target.y;
				                    });
				                node.attr("transform", function (d) {
				                    return "translate(" + d.x + "," + d.y + ")";
				                });
				            });
				            
				            
				            let linkedByIndex = {};
				            json.links.forEach((d) => {
				        	  linkedByIndex[`${d.source.index},${d.target.index}`] = true;
				        	});

				            
				            
				            // / end function
				            
				            
				            
				          }

				 
				        	const forceX = d3.forceX(width / 2).strength(directedForce)
				        	const forceY = d3.forceY(height / 2).strength(directedForce)
				        	
				        	
				        	 // tempValue /d.source.value /d.target.value
				        	
					 var force = d3.forceSimulation() 
					 	// .force("charge", d3.forceManyBody())
					 // .strength(-200))
					 .force("link", 
							 d3.forceLink().id(function(d) { return d.id; })
							 .distance(function(d) { return radius(d.source.r / 2) + radius(d.target.r / 2); })
							 .strength(function(d) {return 2.35; })
					 )
					 .force("charge", d3.forceManyBody().strength(-300))
					 // .force("collide",
						// d3.forceCollide().radius(function(d) { return
						// radius(d.value / 2) + 2; }))
					 .force("collide", d3.forceCollide().radius(function(d) { return radius(d.r/contractionFactor) + 0.5; }).iterations(2))
					 .force("center", d3.forceCenter(width / 2, height / 2));


					
			            if (type == 'mchartsn_node') {
							console.log("create nodes_network from wp : " + type);

							var data = {
									action : 'maxicharts_get_network_nodes',
									post_type : type,
								// posts_offset: posts_offset
								};
								console.log(data);
								$.post(maxicharts_d3js_ajax_object.ajax_url, data,
										function(posts_list) {
										console.log("list of posts retrieved from backend:");
											console.log(posts_list);
								
											processInputDatas(posts_list);

										});
							 
					
							
						}  else {
							d3.json(element, processInputDatas);
						}
			            
			          
					}
				});

			}

 
			console.log("adding buttons listeners");

			console.log("adding buttons listeners done!");
		});