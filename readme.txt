=== MaxiCharts D3js ===
Contributors: maxicharts,munger41
Tags: maxicharts, d3, js, charts, data,driven,documents
Requires at least: 4.0
Tested up to: 5.8

Create beautiful charts using powerfull D3js library. 

== Description ==

Create beautiful sunbursts, networks, trees based on free csv datasheets or dedicated Custom Post Type. Uses the wonderfull js library [d3.js](https://d3js.org/ "d3.js"). 

### Shortcode ###

Create a d3js chart based on json or CPT nodes (created by plugin):

Create sunburst from hierarchical JSON file:

`[maxicharts_d3js show_buttons="" type="sunburst" height="500"]"`

Create tree from hierarchical JSON file:

`[maxicharts_d3js show_buttons="" type="tree" data_path"https://url_of_your_json.json"]`

Create tree from gravity forms form (need conditionnalLogic):

`[maxicharts_d3js show_buttons="" type="tree" gf_form_id="3" width="1600" height="1000"]`

Create network from Network nodes:

`[maxicharts_d3js_nodes show_buttons="" width="960px" height="500px"]`

== Installation ==

### Easy ###
1. Search via plugins > add new.
2. Find the plugin listed and click activate.
3. Use the Shortcodes


== Screenshots ==


== Changelog ==

1.1 - tree folder strcuture from CSV ok, d3 v5 upgraded

1.0.1 - tree integration to GF, need more tests

1.0 - First stable release: tree, network, sunburst. Beta version.