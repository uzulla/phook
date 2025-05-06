# Phook 実装ドキュメント

## 概要

このドキュメントでは、OpenTelemetry PHP拡張機能からPhook PHP拡張機能への変換作業について説明します。変換の主な目的は、OpenTelemetry固有の機能を削除し、メソッドフック機能のみを残すことでした。

## 変更内容

1. **クラス名の変更**
   - `OpenTelemetry\Instrumentation` から `Phook` に名前空間とクラス名を変更
   - すべての関連ファイル名を変更（例：`opentelemetry.c` → `phook.c`）

2. **OpenTelemetry固有の機能の削除**
   - テレメトリデータ送信機能を削除
   - SpanとSpanAttribute関連のコードを削除または簡素化

3. **設定オプションの変更**
   - INI設定名を `opentelemetry.*` から `phook.*` に変更
   - エラーメッセージを更新

4. **内部変数と型の変更**
   - `OTEL_G` マクロを `PHOOK_G` に変更
   - `otel_observer` 構造体を `phook_observer` に変更
   - `otel_arg_locator` を `phook_arg_locator` に変更

## ファイル変更リスト

以下のファイルを変更または作成しました：

1. **名前変更**
   - `opentelemetry.c` → `phook.c`
   - `php_opentelemetry.h` → `php_phook.h`
   - `otel_observer.c` → `phook_observer.c`
   - `otel_observer.h` → `phook_observer.h`
   - `opentelemetry.stub.php` → `phook.stub.php`
   - `opentelemetry_arginfo.h` → `phook_arginfo.h`

2. **主な変更ファイル**
   - `php_phook.h`: モジュールエントリとグローバル変数の定義を更新
   - `phook.stub.php`: 名前空間とドキュメントを更新
   - `phook_observer.h`: ヘッダーガードとプロトタイプ宣言を更新
   - `phook.c`: モジュール関数とINI設定を更新
   - `config.m4`: ビルド設定を更新
   - `phook_arginfo.h`: 関数エントリを更新
   - `phook_observer.c`: オブザーバー関数と型を更新

## ビルド手順

拡張機能は以下の手順でビルドできます：

```bash
cd ext
./build.sh
```

または、Dockerを使用する場合：

```bash
docker compose build debian
docker compose run debian ./build.sh
```

## 今後の課題

1. **テストの追加**
   - 変換後の機能が正しく動作することを確認するためのテストケースの追加

2. **ドキュメントの更新**
   - APIリファレンスの更新
   - 使用例の追加

3. **パフォーマンス最適化**
   - 不要なコードの削除によるパフォーマンス向上の可能性の検討
