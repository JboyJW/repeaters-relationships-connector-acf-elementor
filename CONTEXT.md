# Plugin Context Summary

This document provides a summary of the current state and architecture of the `repeaters-relationships-connector-acf-elementor` plugin.

## Core Purpose

The plugin's goal is to allow an Elementor Loop Grid to use an ACF Repeater or ACF Relationship field as its data source. It also provides dynamic tags to pull sub-field data from the repeater or Relationship within the loop template.

## Key Implementation Details

The plugin is namespaced under `RepRelCon` and the logic is organized into several files within the `includes/` directory. The main plugin file, `repeaters-relationships-connector-acf-elementor.php`, is responsible for loading these files.

- **`includes/register_controls.php`**: Handles the modification of the Elementor Query control to add ACF Repeater and Relationship as query sources.
- **`includes/register_dynamic_tag.php`**: Contains the implementation for the two dynamic tags: `Acf_Repeater_Sub_Field_Tag` and `Acf_Relation_Sub_Field_Tag`.
- **`includes/modify_query_results.php`**: Contains the logic that filters Elementor's query results to inject the ACF repeater or relationship data.

### 1. Modifying the Loop Grid Query Source

- **Problem:** The "Source" dropdown in the Loop Grid's Query tab is not easily extensible via a standard filter.
- **Solution:** We globally replace the `related-query` group control with our own custom class, `Acf_Query_Control_Wrapper`.
    - This is done by hooking into `elementor/controls/register` with a late priority (999) and using `$controls_manager->add_group_control()` to overwrite the existing control.
    - Our custom class extends `\ElementorPro\Modules\QueryControl\Controls\Group_Control_Related` to ensure we inherit all the default functionality.

### 2. Adding Custom Controls

- The `Acf_Query_Control_Wrapper` class overrides the `get_fields_array()` method.
- This method is responsible for:
    - **Adding Sources:** Adding "ACF Repeater" and "ACF Relationship" to the `post_type` control's options.
    - **Adding Conditional Dropdowns:**
        - Defines a `SELECT` control named `acf_repeater_name`, which is conditionally displayed when the source is `acf_repeater`.
        - Defines a `SELECT` control named `acf_relation_name`, which is conditionally displayed when the source is `acf_relation`.
    - **Hiding Controls:** Modifying the `condition` array of several default controls (like Include/Exclude, Date, and Order) to hide them when the source is `acf_repeater` or `acf_relation`.

### 3. Custom Query Execution

- We use the `elementor/query/query_results` filter to intercept the query after it runs.
- **For "ACF Repeater" source:**
    - The Loop Grid expects an array of `WP_Post` objects, but our source is an ACF Repeater (an array of arrays).
    - We get the repeater data from the current post using `get_field()`.
    - We loop through each repeater row and create a new generic `stdClass` object for it, which is then cast to a `WP_Post` object.
    - We map common sub-field names (`title`, `content`) to the corresponding properties on the new object (`post_title`, `post_content`) to allow basic compatibility with standard dynamic tags.
    - **Crucially**, the original, unmodified repeater row array is attached to the new post object under a custom property named `acf_repeater_data`.
    - The original query results are completely replaced with our new array of generated objects.
- **For "ACF Relationship" source:**
    - We get the array of `WP_Post` objects directly from the ACF Relationship field using `get_field()`.
    - This array of post objects simply replaces the original query results. No special object creation is needed.

### 4. Dynamic Tags for Field Data

To access the data within the loop, the plugin registers two separate dynamic tags.

#### ACF Repeater Sub Field Tag

- A dynamic tag named `Acf_Repeater_Sub_Field_Tag` is registered for Text, Image, and URL categories.
- **Controls:** It has a single dropdown control, "Sub Field".
- **Options:** This dropdown is populated with a list of all available repeater and sub-field pairs, formatted as `Repeater Label > Sub-field Label`.
- **Logic:**
    - Its `get_value()` method retrieves the current `post` object within the loop.
    - It checks for our custom `acf_repeater_data` property on that object.
    - It parses the control's setting to extract the sub-field name and uses it to get the correct value from the `acf_repeater_data` array.
    - It correctly handles image sub-fields by returning the ID/URL array that Elementor expects.

#### ACF Relationship Sub Field Tag

- A dynamic tag named `Acf_Relation_Sub_Field_Tag` is registered for Text, Image, and URL categories.
- **Controls:** It has two dropdown controls:
1.  **Relationship Field:** Selects the parent ACF Relationship field.
2.  **Field:** Selects the data to retrieve from the related post.
- **Options:** The "Field" dropdown provides a list of standard post properties (Post Title, Content, Excerpt, Permalink, Featured Image).
- **Logic:**
    - In a relationship query, the `global $post` object inside the loop is the actual `WP_Post` object from the relationship.
    - Its `get_value()` method reads the "Field" control's setting.
    - Based on the selection, it retrieves the corresponding property from the `$post` object (e.g., using `get_the_title($post)` or `get_permalink($post)`).
    - It correctly handles requests for the featured image.
