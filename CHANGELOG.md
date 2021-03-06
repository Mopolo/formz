# ![Formz](Documentation/Images/formz-icon@medium.png) Formz - Changelog

0.2.0 - 2016-11-10
------------------

This release fixes two severe issues (see below), it is **strongly recommended** to upgrade to this version.

A new condition type has also been introduced: `fieldIsEmpty`, which can be used to check whether a field has no value set.

A bunch of unit tests are also embedded in this version, making a step forward the first stable version, which will be released when the tests coverage reaches a correct rate.

----

**Major commits list:**

- **[[5849c42](https://github.com/romm/formz/commit/5849c4241946e673cf0895a1d2c2440eb697a0a3)] [FEATURE] Add new condition `fieldIsEmpty`**

Adding a new condition, with its own JavaScript connector. It is verified when a field has no value, but also when a multiple checkbox has no value checked.

- **[[3e70d83](https://github.com/romm/formz/commit/3e70d8364320a050c59699f66be6c0e8b2f9ce6f)] [BUGFIX] Fix wrong JavaScript call**

A JavaScript debug statement was using the `Debug` library directly, instead of the low-level `debug` function.

It could cause an error when the `Debug` library was not loaded (when the debug mode is not enabled in Formz configuration).

- **[[#26](https://github.com/romm/configuration_object/pull/26)] [BUGFIX] Fix persistent option in field ViewHelper**

When using the ViewHelper `<formz:option>` inside `<formz:field>`, the option would not be deleted after the whole field is processed, resulting in unwanted options in later fields, which could cause pretty ugly behaviours.

For instance, a field could be flagged as required even if it is not.

In the following example, the option `required` would have been present for the field `bar` (it is now fixed by this commit).

```
<formz:field name="foo">
    <formz:option name="required" value="1" />

    [...]
</formz:field>

<formz:field name="bar">
    [...]
</formz:field>
```

0.1.1 - 2016-10-10
------------------

- **[[#1](https://github.com/romm/configuration_object/pull/1)] [TASK] Implement Travis & Coveralls integration**

A continuous integration is now up and running on the Git repository.

Unit tests are being written.

-----

- **[[#5](https://github.com/romm/configuration_object/pull/5)] [DOC] Fix typos in the chapter 5**
- **[[#7](https://github.com/romm/configuration_object/pull/7)] [DOC] Fix typos in the chapter 6**
- **[[#10](https://github.com/romm/configuration_object/pull/10)] [DOC] Fix typos in the chapter 7**
- **[[#11](https://github.com/romm/configuration_object/pull/11)] [DOC] Fix typos in the chapter 8**
- **[[#12](https://github.com/romm/configuration_object/pull/12)] [DOC] fix typos in the readme file**

Many english mistakes were corrected in the documentation. Thanks to [@Mopolo](https://github.com/Mopolo) for his help!
