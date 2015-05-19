# mage-extended-attributes
A magento custom adapter that converts key-value pairs into a simple HTML table. Useful for cases where custom attributes might become too cumbersome. 

## Overview
This custom Dataflow Import Profile leverages the Magento Dataflow Module to create custom logic for your Dataflow imports. In this case the pipe-seperated key value paris are converted into a two column table and added to the custom attribute named "extended_attribute". I developed this for use cases where some products needed additional tabular data to be displayed on the product page without the need for addding and managing a large number of custom attributes. 

## Installation Using Modgit
```bash
#### You can use the slug of your choice, but I prefer using the repo name for best results. 
modgit add mage-extended-attributes https://github.com/flintdigital/mage-extended-attributes.git
```
## CSV Raw Data
There is a sample file in the repo, however to give you an idea of how it works the csv data should look something like this: 
```csv
"store","sku","name","extended_attribute"
"admin","dyn-cham127skis1213","Dynastar Cham 127 Skis","Radius | 22m, Sidecut | 149/127/141 mm, Camber Profile | Rocker/Camber, Core | Wood Fiber, Construction | Sandwich"
```

## XML Snippet

```xml
<action type="dataflow/convert_adapter_io" method="load">
    <var name="type">file</var>
    <var name="path">var/import/extended_attribute</var>
    <var name="filename"><![CDATA[extended_attribute_import.csv]]></var>
    <var name="format"><![CDATA[csv]]></var>
</action>
<action type="dataflow/convert_parser_csv" method="parse">
    <var name="delimiter"><![CDATA[\t]]></var>
    <var name="enclose"><![CDATA["]]></var>
    <var name="fieldnames">true</var>
    <var name="store"><![CDATA[0]]></var>
    <var name="number_of_records">1</var>
    <var name="decimal_separator"><![CDATA[.]]></var>
    <var name="adapter">catalog/convert_adapter_productimport</var>
    <var name="method">parse</var>
</action>
```
## Configuration Instructions
<ul>
<li>Go to System->Import/Export->Dataflow - Advanced Profiles</li>
<li>Create a new Custom Adapter Import Profile </li>
<li>Call it <strong>Extended Attributes Import</strong></li>
<li>Add the Above XML snippet into you the <strong>Actions XML</strong> Input Area</li>

<li>Create the directory var/import/extended_attribute</li>
<li> Create the custom attribute with an id of "extended_attribute" it should be of the type text area</li>
<li>Create a CSV file called extended_attribute_import.csv </li>
<li>Make sure that at a minimum you have the following headers for the csv: sku, name, extended_attribute - The upload will not work otherwise. You can have the type and store columns but they must be set to configurable and store respectively. </li>
<li>Save/Export to a csv - comma delimited and text wrapped in double quotes. (Google docs should do this auto-magically, so I always recommend using google docs to manage the spreadsheet)</li>

<li>Upload file to var/import/extended_attribute</li>
<li>Login into magento and go System -> Import/Export -> Dataflow - Advanced Profiles</li>
<li>Click the run tab and run the profile in the pop-up (orange button)</li>
<li>You should see your results in that window.</li>
</ul>

## Notes
<li>A sample data file and the path to the var/export file is included. You can use the data for testing or a template.</li>
<li>You can tweak the XML snippet to change the file name and location as needed.</li>
