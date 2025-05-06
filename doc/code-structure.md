# PHook拡張機能のコード構造

このドキュメントは、PHook拡張機能のコード構造の概要を提供し、主要なファイル、データ構造、関数を説明します。

## 主要なファイル

| ファイル | 説明 |
|------|-------------|
| `php_phook.h` | モジュール定義とグローバル変数を含むメインヘッダーファイル |
| `phook.c` | 拡張機能の初期化とフック関数の実装 |
| `phook_observer.c` | Zendオブザーバー統合の実装 |
| `phook_observer.h` | オブザーバー機能のヘッダー |
| `phook_arginfo.h` | 公開関数のパラメータ情報 |
| `phook.stub.php` | 拡張機能APIのPHPスタブ |

## コアコンポーネント

### 1. フック登録

フック登録システムは`phook.c`に実装されており、関数とメソッドの傍受用のコールバックを登録する`PHook\hook()`関数を公開しています。

#### 主要な関数:

- `PHP_FUNCTION(phook_hook)`: PHPに公開されるフック関数
- `add_observer()`: オブザーバーシステムにフックを登録する内部関数
- `find_hook_attribute()`: 関数/メソッドのPHP 8+属性を検出
- `find_param_attribute()`: 関数パラメータのPHP 8+属性を検出

### 2. オブザーバーシステム

オブザーバーシステムはZend EngineのオブザーバーAPIと統合して関数呼び出しを傍受し、`phook_observer.c`に実装されています。

#### 主要な関数:

- `observer_fcall_init()`: 関数が実行されようとするときにZendエンジンによって呼び出される
- `observer_fcall_handler()`: 関数実行前フックを処理
- `observer_fcall_end()`: 関数完了後にZendエンジンによって呼び出される
- `phook_observer_init()`: 拡張機能の起動時にオブザーバーシステムを初期化

#### 主要なデータ構造:

- `observer_class_lookup`: クラスメソッドフック用のHashTable
- `observer_function_lookup`: 関数フック用のHashTable
- `observer_aggregates`: すべてのオブザーバーインスタンス用のHashTable

### 3. パラメータ処理

パラメータ処理システムは関数パラメータを管理し、実行前フックによる変更を可能にします。

#### 主要な関数:

- `prepare_params_for_userland()`: ZendパラメータをPHPで使用可能な形式に変換
- `apply_userland_params()`: 実行前フックからのパラメータ変更を適用
- `expand_params()`: 関数定義を超える追加パラメータの処理

### 4. 戻り値処理

戻り値処理システムは関数の戻り値を傍受し、潜在的に変更を処理します。

#### 主要な関数:

- `handle_post_hook()`: 実行後フックコールバックとその戻り値を処理
- `apply_userland_return()`: 実行後フックからの戻り値の変更を適用

### 5. 属性サポート

属性サポートシステムはPHP 8+属性ベースのフックを可能にします。

#### 主要な関数:

- `handle_attributes()`: 拡張機能の初期化中に属性を処理
- `handle_hook_attribute()`: `#[Hook]`属性に基づいてフックを登録
- `handle_capture_param_attribute()`: パラメータキャプチャ属性を処理

## ハッシュテーブル構造

拡張機能は効率的なフック検索のために多層ハッシュテーブル構造を使用しています:

1. 関数の場合:
   ```
   observer_function_lookup[function_name] = hook_data
   ```

2. メソッドの場合:
   ```
   observer_class_lookup[class_name][method_name] = hook_data
   ```

各フックエントリには以下が含まれます:
- 実行前フックコールバック（登録されている場合）
- 実行後フックコールバック（登録されている場合）
- パラメータ処理の設定

## 実行時フロー

1. PHPが初期化され、拡張機能を読み込む
2. 拡張機能がZendオブザーバーシステムに登録
3. 実行時、関数が呼び出されると:
   - Zendエンジンがオブザーバーシステムに通知
   - 拡張機能が登録済みフックを検索
   - 実行前フックが関数パラメータで実行される
   - 元の関数が実行される（潜在的に変更されたパラメータで）
   - 実行後フックが戻り値で実行される
   - 最終的な（潜在的に変更された）戻り値が返される

## 拡張機能のグローバル変数

拡張機能は以下のグローバル設定を使用します（INIで管理）:

```c
ZEND_BEGIN_MODULE_GLOBALS(phook)
    HashTable *observer_class_lookup;
    HashTable *observer_function_lookup;
    HashTable *observer_aggregates;
    int validate_hook_functions;
    char *conflicts;
    int disabled;
    int allow_stack_extension;
    int attr_hooks_enabled;
    int display_warnings;
    char *pre_handler_function_fqn;
    char *post_handler_function_fqn;
ZEND_END_MODULE_GLOBALS(phook)
```

これらのグローバル変数はPHPランタイム全体で拡張機能の状態と設定を管理します。

## 公開されるクラスと関数

| 名前 | 種類 | 説明 |
|------|------|-------------|
| `PHook\hook()` | 関数 | 関数/メソッドフックを登録 |
| `#[Hook]` | 属性 | 関数/メソッドを自動フック用にマーク |
| `#[CaptureParam]` | 属性 | フック中にキャプチャするパラメータをマーク |